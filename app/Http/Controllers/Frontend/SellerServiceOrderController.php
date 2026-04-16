<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\AdminShipmentNeedsReviewMail;
use App\Models\Listing;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SellerServiceOrderController extends Controller
{
    public function __construct(
        protected ServiceOrderService $serviceOrderService
    ) {
    }

    public function index(Request $request)
    {
        $baseQuery = ServiceOrder::with(['listing.photos', 'buyer'])
            ->where('seller_id', auth()->id());

        if ($request->get('view') === 'completed') {
            $serviceOrders = (clone $baseQuery)
                ->where('status', ServiceOrder::STATUS_COMPLETED)
                ->latest()
                ->paginate(12);

            return view('frontend.seller.service-orders.completed', compact('serviceOrders'));
        }

        $agreedOrders = (clone $baseQuery)
            ->where('status', ServiceOrder::STATUS_AGREED)
            ->latest()
            ->get();

        $daromasOrders = (clone $baseQuery)
            ->where('status', ServiceOrder::STATUS_DAROMAS)
            ->latest()
            ->get();

        $readyOrders = (clone $baseQuery)
            ->where('status', ServiceOrder::STATUS_READY_TO_SHIP)
            ->latest()
            ->get();

        return view('frontend.seller.service-orders.index', compact(
            'agreedOrders',
            'daromasOrders',
            'readyOrders'
        ));
    }

    public function create()
    {
        $listings = Listing::query()
            ->where('user_id', auth()->id())
            ->where('tipas', 'paslauga')
            ->latest()
            ->get();

        return view('frontend.seller.service-orders.create', [
            'listing' => null,
            'listings' => $listings,
            'serviceOrder' => null,
        ]);
    }

    public function createFromListing(Listing $listing)
    {
        abort_unless((int) $listing->user_id === (int) auth()->id(), 403);

        $listings = Listing::query()
            ->where('user_id', auth()->id())
            ->where('tipas', 'paslauga')
            ->latest()
            ->get();

        return view('frontend.seller.service-orders.create', [
            'listing' => $listing,
            'listings' => $listings,
            'serviceOrder' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $serviceOrder = $this->serviceOrderService->createFromSeller($data, auth()->user());

        return redirect()
            ->route('seller.service-orders.show', $serviceOrder)
            ->with('success', 'Paslaugos užsakymas sukurtas.');
    }

    public function show(ServiceOrder $serviceOrder)
    {
        abort_unless((int) $serviceOrder->seller_id === (int) auth()->id(), 403);

        $serviceOrder->load(['listing.photos', 'buyer', 'seller']);

        return view('frontend.seller.service-orders.show', compact('serviceOrder'));
    }

    public function edit(ServiceOrder $serviceOrder)
    {
        abort_unless((int) $serviceOrder->seller_id === (int) auth()->id(), 403);

        $listings = Listing::query()
            ->where('user_id', auth()->id())
            ->where('tipas', 'paslauga')
            ->latest()
            ->get();

        return view('frontend.seller.service-orders.create', [
            'listing' => $serviceOrder->listing,
            'listings' => $listings,
            'serviceOrder' => $serviceOrder,
        ]);
    }

    public function update(Request $request, ServiceOrder $serviceOrder)
    {
        $data = $this->validatedData($request);

        $this->serviceOrderService->update($serviceOrder, $data, auth()->user());

        return redirect()
            ->route('seller.service-orders.show', $serviceOrder)
            ->with('success', 'Paslaugos užsakymas atnaujintas.');
    }

    public function updateStatus(Request $request, ServiceOrder $serviceOrder)
    {
        $data = $request->validate([
            'status' => 'required|string|in:agreed,daromas,ready_to_ship,cancelled',
        ]);

        $this->serviceOrderService->updateStatus($serviceOrder, $data['status'], auth()->user());

        return back()->with('success', 'Būsena atnaujinta.');
    }

    public function submitShipment(Request $request, ServiceOrder $serviceOrder)
    {
        abort_unless((int) $serviceOrder->seller_id === (int) auth()->id(), 403);

        $data = $request->validate([
            'tracking_number' => 'required|string|max:255',
            'carrier' => 'required|in:omniva,venipak',
            'package_size' => 'required|in:S,M,L',
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $serviceOrder = $this->serviceOrderService->submitShipmentProof($serviceOrder, $data, auth()->user());

        $admins = User::where('role', 'admin')->pluck('el_pastas');
        if ($admins->isNotEmpty()) {
            Mail::to($admins)->send(new AdminShipmentNeedsReviewMail($serviceOrder));
        }

        return back()->with('success', 'Siuntos įrodymas pateiktas administratoriaus peržiūrai.');
    }

    public function completePrivately(ServiceOrder $serviceOrder)
    {
        $this->serviceOrderService->completePrivately($serviceOrder, auth()->user());

        return redirect()
            ->route('seller.service-orders.index')
            ->with('success', 'Paslaugos užsakymas pažymėtas kaip užbaigtas privačiai.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'listing_id' => 'required|exists:listing,id',
            'is_anonymous' => 'nullable|boolean',
            'buyer_code' => 'nullable|string|max:20',
            'package_size' => 'required|in:S,M,L',
            'final_price' => 'required|numeric|min:0.30',
            'buyer_information' => 'nullable|string|max:2000',
            'agreed_specifications' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
            'shipping_notes' => 'nullable|string|max:5000',
            'custom_requirements' => 'nullable|string|max:5000',
            'timeline_notes' => 'nullable|string|max:5000',
            'other_comments' => 'nullable|string|max:5000',
        ]);
    }
}
