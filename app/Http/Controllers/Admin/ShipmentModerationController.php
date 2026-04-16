<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ReimburseShippingJob;
use App\Models\OrderShipment;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderService;
use Illuminate\Http\Request;

class ShipmentModerationController extends Controller
{
    public function index()
    {
        $shipments = OrderShipment::with([
            'order.user',
            'seller',
            'order.orderItem.listing'
        ])
        ->where('status', 'needs_review')
        ->latest()
        ->paginate(20, ['*'], 'shipments_page');

        $serviceShipments = ServiceOrder::with(['seller', 'buyer', 'listing'])
            ->where('shipment_status', ServiceOrder::SHIPMENT_NEEDS_REVIEW)
            ->latest()
            ->paginate(20, ['*'], 'service_shipments_page');

        return view('admin.shipments.index', compact('shipments', 'serviceShipments'));
    }

    public function approve(OrderShipment $shipment)
    {
        if ($shipment->status !== 'needs_review') {
            return back()->with('error', 'Neteisinga siuntos būsena.');
        }

        $shipment->update(['status' => 'approved']);
        ReimburseShippingJob::dispatch($shipment->id);

        return back()->with('success', 'Siunta patvirtinta.');
    }

    public function reject(Request $request, OrderShipment $shipment)
    {
        $shipment->update(['status' => 'pending']);

        return back()->with('success', 'Siunta atmesta. Pardavėjas turi pateikti iš naujo.');
    }

    public function approveService(ServiceOrder $serviceOrder, ServiceOrderService $serviceOrderService)
    {
        $serviceOrderService->approveShipment($serviceOrder);

        return back()->with('success', 'Paslaugos užsakymo siunta patvirtinta ir užsakymas užbaigtas.');
    }

    public function rejectService(ServiceOrder $serviceOrder, ServiceOrderService $serviceOrderService)
    {
        $serviceOrderService->rejectShipment($serviceOrder);

        return back()->with('success', 'Paslaugos užsakymo siunta atmesta. Pardavėjas turi pateikti iš naujo.');
    }
}
