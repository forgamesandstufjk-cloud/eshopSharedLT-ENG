<x-app-layout>
    <div class="min-h-screen w-full mt-10 pb-10" style="background-color: rgb(234, 220, 200)">
        <div class="max-w-6xl mx-auto container relative z-10">
        
        <h1 class="text-2xl font-bold mb-6 text-black">Mano pirkimai</h1>

        @php
            $hasProductOrders = $orders->isNotEmpty();
            $hasServiceOrders = $serviceOrders->isNotEmpty();
        @endphp

        {{-- PREKIŲ UŽSAKYMAI --}}
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

                {{-- ITEMS --}}
                <div class="border-t pt-3" style="border-color: #836354">
                    @foreach($order->orderItem as $item)
                        <div class="flex justify-between text-sm mb-1 text-black">
                            <span>
                                {{ $item->Listing->pavadinimas }}
                                <span class="text-black">
                                    (Pardavėjas: {{ $item->Listing->user->vardas }})
                                </span>
                            </span>
                            <span>
                                €{{ number_format($item->kaina * $item->kiekis, 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- SHIPMENTS --}}
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

        {{-- PASLAUGŲ UŽSAKYMAI --}}
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
                    <div class="flex justify-between text-sm text-black">
                        <span>
                            {{ $serviceOrder->original_listing_title }}
                            <span class="text-black">
                                (Pardavėjas: {{ $serviceOrder->seller->vardas ?? $serviceOrder->seller->name }})
                            </span>
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
                            @if($serviceOrder->status === \App\Models\ServiceOrder::STATUS_READY_TO_SHIP
                                && $serviceOrder->payment_status !== \App\Models\ServiceOrder::PAYMENT_PAID)
                                <span class="px-2 py-1 text-xs rounded text-black" style="background-color: rgb(234, 220, 200)">
                                    Laukia apmokėjimo
                                </span>
                    
                            @elseif($serviceOrder->payment_status === \App\Models\ServiceOrder::PAYMENT_PAID
                                && in_array($serviceOrder->shipment_status, [null, \App\Models\ServiceOrder::SHIPMENT_PENDING], true))
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
                    
                    @if($serviceOrder->status === \App\Models\ServiceOrder::STATUS_READY_TO_SHIP
                        && $serviceOrder->payment_status !== \App\Models\ServiceOrder::PAYMENT_PAID)
                        <div class="pt-2">
                            <a
                                href="{{ route('checkout.index', ['service_order' => $serviceOrder->id]) }}"
                                class="inline-block text-white px-4 py-2 rounded hover:text-black"
                                style="background-color: rgb(131, 99, 84)"
                            >
                                Apmokėti per svetainę
                            </a>
                        </div>
                    @endif

                    @if(in_array($serviceOrder->shipment_status, [
                        \App\Models\ServiceOrder::SHIPMENT_APPROVED,
                        \App\Models\ServiceOrder::SHIPMENT_REIMBURSED
                    ], true) && $serviceOrder->tracking_number)
                        <div class="text-xs text-black ml-2">
                            Siuntos sekimas: {{ $serviceOrder->tracking_number }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if(!$hasProductOrders && !$hasServiceOrders)
            <div class="shadow rounded p-6 text-center text-black" style="background-color: rgb(215, 183, 142)">
               Jūs dar nieko nepirkote.
            </div>
        @endif

        </div>
    </div>
</x-app-layout>
