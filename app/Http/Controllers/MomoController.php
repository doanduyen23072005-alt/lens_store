<?php
// app/Http/Controllers/MomoController.php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\GHNOrderService;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
{
    // ==========================================
    // 1. BẮT ĐẦU / THANH TOÁN LẠI
    // ==========================================

    /** Bắt đầu thanh toán ngay sau khi đặt hàng. */
    public function start(Order $order, MomoService $momo)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        return $this->redirectToMomo($order, $this->newTransaction($order), $momo);
    }

    /** Thanh toán lại đơn đã thất bại — KHÔNG tạo đơn hàng mới. */
    public function payAgain(Order $order, MomoService $momo)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if ($order->status === 'paid') {
            return redirect()->route('order.show', $order)
                ->with('info', 'Đơn hàng này đã được thanh toán.');
        }

        if ($order->status === 'cancelled') {
            return redirect()->route('order.show', $order)
                ->with('error', 'Đơn hàng đã hủy, không thể thanh toán.');
        }

        return $this->redirectToMomo($order, $this->newTransaction($order), $momo);
    }

    // ==========================================
    // 2. NHẬN KẾT QUẢ TỪ MOMO
    // ==========================================

    /** Khách được MoMo chuyển về sau khi thao tác xong (chạy trên trình duyệt). */
    public function callback(Request $request, GHNOrderService $ghnOrders, MomoService $momo)
    {
        Log::info('MoMo callback received', [
            'payload'       => $request->except('signature'),
            'has_signature' => $request->has('signature'),
        ]);

        $orderId = $momo->orderId($request->all());

        if (!$momo->isValidSuccessfulResponse($request->all())) {
            Log::warning('MoMo callback rejected', [
                'result_code'     => $request->input('resultCode'),
                'order_id'        => $request->input('orderId'),
                'signature_valid' => $momo->isValidResponse($request->all()),
            ]);

            // Chi ghi nhan that bai khi chu ky hop le, tranh bi gia mao
            if ($momo->isValidResponse($request->all())) {
                $this->markFailed($request->all(), $momo);
            }

            return $orderId
                ? redirect()->route('order.show', $orderId)
                    ->with('error', 'Giao dịch MoMo thất bại. Bạn có thể thử thanh toán lại.')
                : redirect()->route('order.index')->with('error', 'Giao dịch MoMo thất bại.');
        }

        $result = $this->completePayment($request->all(), $ghnOrders, $momo);

        $message = in_array($result, ['created', 'already_created'], true)
            ? 'Thanh toán MoMo thành công! Vận đơn GHN đã được khởi tạo.'
            : 'Thanh toán thành công! Đơn hàng đang chờ tạo vận đơn GHN.';

        return $orderId
            ? redirect()->route('order.show', $orderId)->with('success', $message)
            : redirect()->route('order.index')->with('success', $message);
    }

    /** MoMo gọi server-to-server. Đây mới là nguồn tin cậy để cập nhật DB. */
    public function ipn(Request $request, GHNOrderService $ghnOrders, MomoService $momo)
    {
        Log::info('MoMo IPN received', [
            'payload'       => $request->except('signature'),
            'has_signature' => $request->has('signature'),
        ]);

        if ($momo->isValidSuccessfulResponse($request->all())) {
            $this->completePayment($request->all(), $ghnOrders, $momo);
        } elseif ($momo->isValidResponse($request->all())) {
            $this->markFailed($request->all(), $momo);
        }

        return response()->json(['message' => 'Received']);
    }

    // ==========================================
    // HELPER
    // ==========================================

    /** Mỗi lần thử thanh toán là một bản ghi giao dịch riêng. */
    private function newTransaction(Order $order): PaymentTransaction
    {
        return PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway'  => 'momo',
            'amount'   => $order->total_price,
            'status'   => 'pending',
        ]);
    }

    private function redirectToMomo(Order $order, PaymentTransaction $transaction, MomoService $momo)
    {
        $result = $momo->createPayment($order, $transaction);

        if (isset($result['payUrl'])) {
            return redirect($result['payUrl']);
        }

        Log::error('MoMo createPayment failed', ['order_id' => $order->id, 'result' => $result]);

        return redirect()->route('order.show', $order)
            ->with('error', 'Không thể kết nối tới MoMo. Vui lòng thử lại.');
    }

    /**
     * Xác nhận thanh toán và tạo vận đơn GHN.
     *
     * MoMo gọi CẢ callback lẫn IPN gần như đồng thời cho cùng một giao dịch,
     * nên phải khóa dòng và kiểm tra ghn_order_code để không tạo 2 vận đơn.
     *
     * @return string created | already_created | processing | failed | invalid
     */
    private function completePayment(array $payload, GHNOrderService $ghnOrders, MomoService $momo): string
    {
        $result = DB::transaction(function () use ($payload, $momo) {
            $transaction = PaymentTransaction::where('gateway', 'momo')
                ->where('gateway_order_id', $payload['orderId'] ?? '')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                return 'invalid';
            }

            $order = Order::lockForUpdate()->find($transaction->order_id);

            if (!$order) {
                return 'invalid';
            }

            // Da xu ly roi
            if ($order->ghn_order_code) {
                return 'already_created';
            }

            // Luong kia dang xu ly
            if ($order->shipping_status === 'processing') {
                return 'processing';
            }

            // Doi chieu so tien: phai khop tuyet doi voi tong don (da gom phi ship)
            if ((int) $transaction->amount !== (int) ($payload['amount'] ?? 0)) {
                Log::error('MoMo lech so tien', [
                    'order_id' => $order->id,
                    'momo'     => $payload['amount'] ?? null,
                    'db'       => $transaction->amount,
                ]);

                $momo->markFailed($transaction, $payload);

                return 'invalid';
            }

            $order->update([
                'status'          => 'paid',
                'paid_at'         => now(),
                'shipping_status' => 'processing',
            ]);

            $momo->markPaid($transaction, $payload);

            return ['create', $order->id];
        });

        if (!is_array($result)) {
            return (string) $result;
        }

        $order = Order::with('items.product')->find($result[1]);

        // isPaid = true  =>  cod_amount gui GHN bang 0 vi khach da tra du
        $response = $ghnOrders->create($order, true);

        if (($response['code'] ?? null) === 200 && !empty($response['data']['order_code'])) {
            $order->update([
                'ghn_order_code'  => $response['data']['order_code'],
                'ghn_total_fee'   => (int) ($response['data']['total_fee'] ?? $order->ghn_total_fee),
                'shipping_status' => 'ready_to_pick',
            ]);

            return 'created';
        }

        Log::error('GHN order failed after MoMo payment', [
            'order_id' => $order->id,
            'response' => $response,
        ]);

        $order->update(['shipping_status' => 'not_shipped']);

        return 'failed';
    }

    private function markFailed(array $payload, MomoService $momo): void
    {
        $transaction = PaymentTransaction::where('gateway', 'momo')
            ->where('gateway_order_id', $payload['orderId'] ?? '')
            ->first();

        if ($transaction && $transaction->status !== 'paid') {
            $momo->markFailed($transaction, $payload);
        }
    }
}