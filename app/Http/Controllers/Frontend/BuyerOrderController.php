<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ServiceOrder;

class BuyerOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with([
                'orderItem.Listing.user',
                'shipments.seller',
            ])
            ->where('user_id', auth()->id())
            ->where('statusas', Order::STATUS_PAID)
            ->latest()
            ->paginate(8, ['*'], 'orders_page');

        $serviceOrders = ServiceOrder::with([
                'seller',
            ])
            ->where('buyer_id', auth()->id())
            ->where('status', '!=', ServiceOrder::STATUS_CANCELLED)
            ->latest()
            ->paginate(8, ['*'], 'service_orders_page');

        $hasServiceOrders = ServiceOrder::where('buyer_id', auth()->id())
            ->where('status', '!=', ServiceOrder::STATUS_CANCELLED)
            ->exists();

        return view('frontend.buyer.orders.index', compact(
            'orders',
            'serviceOrders',
            'hasServiceOrders'
        ));
    }
}
