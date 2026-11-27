<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\SlotPickup;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $isStaff = in_array($user->role, ['admin', 'kasir'], true);
        $orders = Pesanan::query();

        if (! $isStaff) {
            $orders->where('user_id', $user->id);
        }

        return view('dashboard.index', [
            'totalOrders' => (clone $orders)->count(),
            'todayOrders' => (clone $orders)->whereDate('created_at', today())->count(),
            'pendingOrders' => (clone $orders)->whereIn('status', ['pending', 'processing'])->count(),
            'todayRevenue' => (clone $orders)
                ->whereDate('created_at', today())
                ->where('status', '!=', 'cancelled')
                ->sum('total_price'),
            'serviceCount' => $isStaff ? Layanan::count() : null,
            'slotCount' => $isStaff ? SlotPickup::whereDate('transaction_date', '>=', today())->count() : null,
            'latestOrders' => (clone $orders)
                ->with(['layanan', 'user'])
                ->latest()
                ->take(8)
                ->get(),
            'isStaff' => $isStaff,
        ]);
    }
}
