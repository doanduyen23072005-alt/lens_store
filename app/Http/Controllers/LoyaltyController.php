<?php
// app/Http/Controllers/LoyaltyController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    /** Trang điểm thân thiết & xếp hạng thành viên của khách. */
    public function index()
    {
        $user = Auth::user();

        $transactions = $user->loyaltyTransactions()->latest()->paginate(10);

        return view('loyalty.index', [
            'user'         => $user,
            'transactions' => $transactions,
            'tiers'        => \App\Models\User::LOYALTY_TIERS,
        ]);
    }
}
