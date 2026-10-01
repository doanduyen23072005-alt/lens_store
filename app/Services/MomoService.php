<?php
// app/Services/MomoService.php
namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    /**
     * Tạo yêu cầu thanh toán MoMo.
     * Trả về mảng response của MoMo, có key 'payUrl' nếu thành công.
     */
    public function createPayment(Order $order, PaymentTransaction $transaction): array
    {
        $endpoint    = config('services.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = config('services.momo.partner_code', '');
        $accessKey   = config('services.momo.access_key', '');
        $secretKey   = config('services.momo.secret_key', '');

        $orderInfo   = 'Thanh toan don hang #' . $order->id;
        $amount      = (string) ((int) $order->total_price);   // DA GOM PHI SHIP
        $orderId     = $order->id . '_' . $transaction->id . '_' . time();
        $redirectUrl = config('services.momo.redirect_url') ?: route('momo.callback');
        $ipnUrl      = config('services.momo.ipn_url') ?: route('momo.ipn');
        $extraData   = (string) $order->id;
        $requestId   = (string) time();
        $requestType = 'payWithCC';

        // Thu tu cac truong trong chuoi hash la BAT BUOC, khong duoc doi
        $rawHash = 'accessKey=' . $accessKey .
            '&amount=' . $amount .
            '&extraData=' . $extraData .
            '&ipnUrl=' . $ipnUrl .
            '&orderId=' . $orderId .
            '&orderInfo=' . $orderInfo .
            '&partnerCode=' . $partnerCode .
            '&redirectUrl=' . $redirectUrl .
            '&requestId=' . $requestId .
            '&requestType=' . $requestType;

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'Lens Store',
            'storeId'     => 'LensStore',
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => hash_hmac('sha256', $rawHash, $secretKey),
        ];

        $transaction->update([
            'gateway_order_id' => $orderId,
            'request_payload'  => $data,
        ]);

        try {
             $response = Http::withOptions([
                'verify' => filter_var(config('services.momo.verify_ssl', true), FILTER_VALIDATE_BOOLEAN),
            ])
                ->connectTimeout(30)
                ->timeout(60)
                ->retry(3, 2000)
                ->post($endpoint, $data);

            $result = $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('Khong ket noi duoc MoMo', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            $transaction->update([
                'status'  => 'failed',
                'message' => 'Không kết nối được tới MoMo.',
            ]);

            return [];
        }

        $transaction->update([
            'response_payload' => $result,
            'result_code'      => isset($result['resultCode']) ? (int) $result['resultCode'] : null,
            'message'          => $result['message'] ?? null,
            'status'           => isset($result['payUrl']) ? 'initiated' : 'failed',
        ]);

        return $result;
    }

    /** MoMo báo thanh toán thành công hay không (resultCode = 0). */
    public function isSuccessful(array $payload): bool
    {
        return (string) ($payload['resultCode'] ?? '') === '0';
    }

    /** Cập nhật giao dịch sau khi thanh toán thành công. */
    public function markPaid(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'transaction_id'   => $payload['transId'] ?? null,
            'result_code'      => (int) ($payload['resultCode'] ?? 0),
            'message'          => $payload['message'] ?? null,
            'response_payload' => $payload,
            'status'           => 'paid',
            'paid_at'          => Carbon::now(),
        ]);
    }

    /** Cập nhật giao dịch thất bại hoặc bị hủy. */
    public function markFailed(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'transaction_id'   => $payload['transId'] ?? null,
            'result_code'      => isset($payload['resultCode']) ? (int) $payload['resultCode'] : null,
            'message'          => $payload['message'] ?? null,
            'response_payload' => $payload,
            'status'           => 'failed',
        ]);
    }

    /** Callback hợp lệ VÀ báo thành công. */
    public function isValidSuccessfulResponse(array $payload): bool
    {
        return $this->isValidResponse($payload) && $this->isSuccessful($payload);
    }

    /**
     * Kiểm tra chữ ký callback/IPN — chống giả mạo kết quả thanh toán.
     * Không có bước này thì ai cũng có thể gọi URL callback để đánh dấu đơn đã trả tiền.
     */
    public function isValidResponse(array $payload): bool
    {
        if (!isset($payload['signature'])) {
            return false;
        }

        $accessKey = config('services.momo.access_key', '');
        $secretKey = config('services.momo.secret_key', '');

        $rawHash = 'accessKey=' . $accessKey .
            '&amount='       . ($payload['amount'] ?? '') .
            '&extraData='    . ($payload['extraData'] ?? '') .
            '&message='      . ($payload['message'] ?? '') .
            '&orderId='      . ($payload['orderId'] ?? '') .
            '&orderInfo='    . ($payload['orderInfo'] ?? '') .
            '&orderType='    . ($payload['orderType'] ?? '') .
            '&partnerCode='  . ($payload['partnerCode'] ?? '') .
            '&payType='      . ($payload['payType'] ?? '') .
            '&requestId='    . ($payload['requestId'] ?? '') .
            '&responseTime=' . ($payload['responseTime'] ?? '') .
            '&resultCode='   . ($payload['resultCode'] ?? '') .
            '&transId='      . ($payload['transId'] ?? '');

        return hash_equals(
            hash_hmac('sha256', $rawHash, $secretKey),
            (string) $payload['signature']
        );
    }

    /** Lấy ID đơn hàng nội bộ từ trường extraData. */
    public function orderId(array $payload): ?int
    {
        $orderId = $payload['extraData'] ?? null;

        return is_numeric($orderId) ? (int) $orderId : null;
    }
}