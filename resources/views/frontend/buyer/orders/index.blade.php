<x-app-layout>
  <div class="min-h-screen flex flex-col" style="background-color: rgb(234, 220, 200)">
    <div class="max-w-6xl mx-auto container relative z-10 px-3 sm:px-0 flex-1 w-full mt-10 pb-10">

            <h1 class="text-2xl font-bold mb-6 text-black">Mano pirkimai</h1>

            @php
                $hasProductOrders = $orders->isNotEmpty();
                $productReviewTracker = [];
            @endphp

            {{-- PREKIŲ UŽSAKYMAI --}}
            @if($hasProductOrders)
                <h2 class="text-xl sm:text-2xl font-bold mb-4 text-black">Prekių užsakymai</h2>

                @foreach($orders as $order)
                    <div class="shadow rounded mb-6 p-5" style="background-color: rgb(215, 183, 142)">
                        <div class="flex justify-between mb-3">
                            <div>
                                <div class="font-semibold text-black">Užsakymas #{{ $order->id }}</div>
                                <div class="text-sm text-black">
                                    {{ $order->pirkimo_data?->format('Y-m-d H:i') }}
                                </div>
                            </div>
                            <div class="font-semibold text-black">
                                €{{ number_format($order->amount_charged_cents / 100, 2) }}
                            </div>
                        </div>

                      <div class="border-t pt-3" style="border-color: #836354">
                        @foreach($order->orderItem as $item)
                          @php
                            $itemListing = $item->Listing;

                            $itemShipment = $itemListing
                                ? $order->shipments->firstWhere('seller_id', $itemListing->user_id)
                                : null;

                            $shipmentDelivered = $itemShipment && in_array($itemShipment->status, ['approved', 'reimbursed'], true);

                            $listingCanBeOpened = $itemListing
                                && !$itemListing->is_hidden
                                && $itemListing->statusas !== 'parduotas';

                            $productReviewWindowOpen = $itemListing
                                && ($itemListing->is_renewable || (int) $itemListing->kiekis >= 1);

                            $showProductReviewStatus = $shipmentDelivered && $listingCanBeOpened;

                            $availableProductReviewSlotsForThisRow = 0;

                            if ($itemListing && !array_key_exists($itemListing->id, $productReviewTracker)) {
                                $userReviewCountForListing = (int) $itemListing->review
                                    ->where('user_id', auth()->id())
                                    ->count();

                                $productPurchaseCountForListing = (int) \App\Models\OrderItem::query()
                                    ->where('listing_id', $itemListing->id)
                                    ->whereHas('order', function ($q) {
                                        $q->where('user_id', auth()->id())
                                          ->where('statusas', \App\Models\Order::STATUS_PAID);
                                    })
                                    ->whereHas('order.shipments', function ($q) use ($itemListing) {
                                        $q->where('seller_id', $itemListing->user_id)
                                          ->whereIn('status', ['approved', 'reimbursed']);
                                    })
                                    ->sum('kiekis');

                                $productReviewTracker[$itemListing->id] = [
                                    'remaining' => max(0, $productPurchaseCountForListing - $userReviewCountForListing),
                                ];
                            }

                            if (
                                $itemListing
                                && $showProductReviewStatus
                                && $productReviewWindowOpen
                                && ($productReviewTracker[$itemListing->id]['remaining'] ?? 0) > 0
                            ) {
                                $availableProductReviewSlotsForThisRow = min(
                                    (int) $item->kiekis,
                                    (int) $productReviewTracker[$itemListing->id]['remaining']
                                );

                                $productReviewTracker[$itemListing->id]['remaining'] -= $availableProductReviewSlotsForThisRow;
                            }

                            $canLeaveProductReviewNow = $availableProductReviewSlotsForThisRow > 0;
                        @endphp

                        <div class="flex justify-between text-sm mb-1 text-black">
                            <span>
                                {{ $item->Listing->pavadinimas }}
                            </span>
                            <span>
                                €{{ number_format($item->kaina * $item->kiekis, 2) }}
                            </span>
                        </div>

                        @if($showProductReviewStatus)
                            <div class="ml-2 mb-2 flex flex-wrap items-center gap-2 text-xs">
                                <span class="px-2 py-1 rounded text-black"
                                      style="background-color:
                                        {{ $canLeaveProductReviewNow
                                            ? 'rgb(234, 220, 200)'
                                            : 'rgb(207, 174, 134)' }}">
                                    @if($canLeaveProductReviewNow)
                                        @if($availableProductReviewSlotsForThisRow === 1)
                                            Dar nepalikote atsiliepimo
                                        @else
                                            Galite palikti dar {{ $availableProductReviewSlotsForThisRow }} atsiliepimą(-ų)
                                        @endif
                                    @elseif(!$productReviewWindowOpen)
                                        Atsiliepimas šiam skelbimui dabar negalimas
                                    @else
                                        {{ (int) $item->kiekis > 1 ? 'Atsiliepimai jau palikti' : 'Atsiliepimas jau paliktas' }}
                                    @endif
                                </span>

                                <a href="{{ route('listing.single', $item->Listing->id) }}"
                                   class="underline"
                                   style="color: rgb(131, 99, 84)">
                                    {{ $canLeaveProductReviewNow ? 'Palikti atsiliepimą' : 'Peržiūrėti skelbimą' }}
                                </a>
                            </div>
                        @endif
                      @endforeach
                    </div>

                        <div class="border-t mt-3 pt-3 space-y-2" style="border-color: #836354">
                            @foreach($order->shipments as $shipment)
                                <div class="text-sm flex justify-between items-center text-black">
                                    <div>
                                        <span class="font-medium">
                                            Siunta nuo {{ $shipment->seller->vardas }}
                                        </span>
                                        <span class="text-black">
                                            ({{ strtoupper($shipment->carrier) }})
                                        </span>
                                    </div>

                                    <div>
                                        @if($shipment->status === 'pending')
                                            <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(234, 220, 200)">
                                                Laukiama išsiuntimo
                                            </span>
                                        @elseif($shipment->status === 'needs_review')
                                            <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(207, 174, 134)">
                                                Laukiama administratoriaus patvirtinimo
                                            </span>
                                        @elseif(in_array($shipment->status, ['approved', 'reimbursed']))
                                            <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(207, 174, 134)">
                                                Išsiųsta
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if(in_array($shipment->status, ['approved', 'reimbursed']) && $shipment->tracking_number)
                                    <div class="text-xs text-black ml-2">
                                        Siuntos sekimas: {{ $shipment->tracking_number }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="mt-4 mb-10 text-black">
                    {{ $orders->appends(request()->except('orders_page'))->links() }}
                </div>
            @endif

           {{-- PASLAUGŲ UŽSAKYMAI --}}
                @if($hasServiceOrders)
                    @php
                        $serviceReviewTracker = [];
                    @endphp

                    <h2 id="service-orders-section" class="text-xl sm:text-2xl font-bold mb-4 text-black">
                        Paslaugų užsakymai
                    </h2>

                    @foreach($serviceOrders as $serviceOrder)
                        <div class="shadow rounded mb-6 p-5" style="background-color: rgb(215, 183, 142)">
                            <div class="flex justify-between mb-3">
                                <div>
                                    <div class="font-semibold text-black">Paslaugos užsakymas #{{ $serviceOrder->id }}</div>
                                    <div class="text-sm text-black">
                                        {{ $serviceOrder->created_at?->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                                <div class="font-semibold text-black">
                                    €{{ number_format((float) $serviceOrder->final_price, 2) }}
                                </div>
                            </div>

                          <div class="border-t pt-3 space-y-2" style="border-color: #836354">
                             @php
                                $serviceListing = $serviceOrder->listing;

                                $serviceReviewEligibleByStatus =
                                    in_array($serviceOrder->shipment_status, [
                                        \App\Models\ServiceOrder::SHIPMENT_APPROVED,
                                        \App\Models\ServiceOrder::SHIPMENT_REIMBURSED,
                                    ], true)
                                    || (
                                        $serviceOrder->completion_method === \App\Models\ServiceOrder::COMPLETION_PRIVATE
                                        && $serviceOrder->status === \App\Models\ServiceOrder::STATUS_COMPLETED
                                    );

                                $listingCanBeOpened = $serviceListing
                                    && !$serviceListing->is_hidden
                                    && $serviceListing->statusas !== 'parduotas';

                                $showServiceReviewStatus = $serviceReviewEligibleByStatus && $listingCanBeOpened;

                                $availableServiceReviewSlotsForThisRow = 0;

                                if ($serviceListing && !array_key_exists($serviceListing->id, $serviceReviewTracker)) {
                                    $userReviewCountForServiceListing = (int) $serviceListing->review
                                        ->where('user_id', auth()->id())
                                        ->count();

                                    $servicePurchaseCountForListing = (int) \App\Models\ServiceOrder::query()
                                        ->where('listing_id', $serviceListing->id)
                                        ->where('buyer_id', auth()->id())
                                        ->where(function ($q) {
                                            $q->where('payment_status', \App\Models\ServiceOrder::PAYMENT_PAID)
                                              ->orWhere(function ($q2) {
                                                  $q2->where('completion_method', \App\Models\ServiceOrder::COMPLETION_PRIVATE)
                                                     ->where('status', \App\Models\ServiceOrder::STATUS_COMPLETED);
                                              });
                                        })
                                        ->count();

                                    $serviceReviewTracker[$serviceListing->id] = [
                                        'remaining' => max(0, $servicePurchaseCountForListing - $userReviewCountForServiceListing),
                                    ];
                                }

                                if (
                                    $serviceListing
                                    && $showServiceReviewStatus
                                    && ($serviceReviewTracker[$serviceListing->id]['remaining'] ?? 0) > 0
                                ) {
                                    $availableServiceReviewSlotsForThisRow = 1;
                                    $serviceReviewTracker[$serviceListing->id]['remaining'] -= 1;
                                }

                                $canLeaveServiceReviewNow = $availableServiceReviewSlotsForThisRow > 0;
                            @endphp

                              <div class="flex justify-between text-sm text-black">
                                  <span>
                                      {{ $serviceOrder->original_listing_title }}
                                  </span>
                                  <span>
                                      €{{ number_format((float) $serviceOrder->final_price, 2) }}
                                  </span>
                              </div>

                              <div class="text-sm flex justify-between items-center text-black">
                                  <div>
                                      <span class="font-medium">Būsena</span>
                                  </div>

                                  <div>
                                      @if($serviceOrder->status === \App\Models\ServiceOrder::STATUS_READY_TO_SHIP)
                                          @if($serviceOrder->completion_method === \App\Models\ServiceOrder::COMPLETION_PLATFORM)
                                              @if($serviceOrder->payment_status === \App\Models\ServiceOrder::PAYMENT_PAID)
                                                  @if(in_array($serviceOrder->shipment_status, [null, \App\Models\ServiceOrder::SHIPMENT_PENDING], true))
                                                      <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(234, 220, 200)">
                                                          Apmokėta, laukiama išsiuntimo
                                                      </span>
                                                  @elseif($serviceOrder->shipment_status === \App\Models\ServiceOrder::SHIPMENT_NEEDS_REVIEW)
                                                      <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(207, 174, 134)">
                                                          Laukiama administratoriaus patvirtinimo
                                                      </span>
                                                  @elseif(in_array($serviceOrder->shipment_status, [
                                                      \App\Models\ServiceOrder::SHIPMENT_APPROVED,
                                                      \App\Models\ServiceOrder::SHIPMENT_REIMBURSED
                                                  ], true))
                                                      <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(207, 174, 134)">
                                                          Išsiųsta
                                                      </span>
                                                  @else
                                                      <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(234, 220, 200)">
                                                          Apmokėta
                                                      </span>
                                                  @endif
                                              @else
                                                  <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(234, 220, 200)">
                                                      Laukia apmokėjimo per svetainę
                                                  </span>
                                              @endif

                                          @elseif($serviceOrder->completion_method === \App\Models\ServiceOrder::COMPLETION_PRIVATE)
                                              <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(234, 220, 200)">
                                                  Užbaikite privačiai sutartu būdu
                                              </span>

                                          @else
                                              <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(234, 220, 200)">
                                                  Laukiama kol pardavėjas pasirinks atsyskaitymo būdą
                                              </span>
                                          @endif

                                      @elseif($serviceOrder->status === \App\Models\ServiceOrder::STATUS_COMPLETED)
                                          <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(207, 174, 134)">
                                              Užbaigta
                                          </span>

                                      @else
                                          <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(234, 220, 200)">
                                              {{ $serviceOrder->lithuanian_status }}
                                          </span>
                                      @endif
                                  </div>
                              </div>

                              @if(
                                  $serviceOrder->status === \App\Models\ServiceOrder::STATUS_READY_TO_SHIP &&
                                  $serviceOrder->completion_method === \App\Models\ServiceOrder::COMPLETION_PLATFORM &&
                                  $serviceOrder->payment_status !== \App\Models\ServiceOrder::PAYMENT_PAID
                              )
                                  <a
                                      href="{{ route('checkout.index', ['service_order' => $serviceOrder->id]) }}"
                                      class="inline-block text-white px-4 py-2 rounded hover:text-black"
                                      style="background-color: rgb(131, 99, 84)"
                                  >
                                      Apmokėti per svetainę
                                  </a>
                              @endif

                              @if(in_array($serviceOrder->shipment_status, [
                                  \App\Models\ServiceOrder::SHIPMENT_APPROVED,
                                  \App\Models\ServiceOrder::SHIPMENT_REIMBURSED
                              ], true) && $serviceOrder->tracking_number)
                                  <div class="text-xs text-black ml-2">
                                      Siuntos sekimas: {{ $serviceOrder->tracking_number }}
                                  </div>
                              @endif

                              @if($showServiceReviewStatus)
                                <div class="pt-2 flex flex-wrap items-center gap-2 text-xs">
                                    <span class="px-2 py-1 rounded text-black"
                                          style="background-color:
                                            {{ $canLeaveServiceReviewNow
                                                ? 'rgb(234, 220, 200)'
                                                : 'rgb(207, 174, 134)' }}">
                                        @if($canLeaveServiceReviewNow)
                                            Dar nepalikote atsiliepimo
                                        @else
                                            Atsiliepimas jau paliktas
                                        @endif
                                    </span>

                                    <a href="{{ route('listing.single', $serviceOrder->listing->id) }}"
                                       class="underline"
                                       style="color: rgb(131, 99, 84)">
                                        {{ $canLeaveServiceReviewNow ? 'Palikti atsiliepimą' : 'Peržiūrėti skelbimą' }}
                                    </a>
                                </div>
                            @endif
                          </div>
                        </div>
                    @endforeach

                    <div class="mt-4 text-black">
                        {{ $serviceOrders->appends(request()->except('service_orders_page'))->fragment('service-orders-section')->links() }}
                    </div>
                @endif

            @if(!$hasProductOrders && !$hasServiceOrders)
                <div class="shadow rounded p-6 text-center text-black" style="background-color: rgb(215, 183, 142)">
                    Jūs dar nieko nepirkote.
                </div>
            @endif

        </div>
          @include('components.footer')
    </div>
</x-app-layout>
