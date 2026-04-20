<x-app-layout>
    <div class="min-h-screen w-full max-w-6xl mx-auto mt-6 pb-10 sm:mt-10 px-3 sm:px-0" style="background-color: rgb(234, 220, 200)">
        <div class="container mx-auto relative z-10">

            <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-black">Mano pardavimai ir siuntos</h1>

            @if(session('success'))
                <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(207, 174, 134)">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(230, 190, 190)">
                    {{ session('error') }}
                </div>
            @endif

            {{-- PAPRASTI UŽSAKYMAI / SIUNTOS --}}
            <div class="shadow rounded overflow-hidden" style="background-color: rgb(215, 183, 142)">
                <table class="w-full text-sm text-black">
                    <thead class="border-b hidden sm:table-header-group" style="background-color: rgb(131, 99, 84); border-color: #836354">
                        <tr>
                            <th class="p-3 text-left text-white">Užsakymas</th>
                            <th class="p-3 text-left text-white">Prekės</th>
                            <th class="p-3 text-left text-white">Pristatymas</th>
                            <th class="p-3 text-left text-white">Pristatymo adresas</th>
                            <th class="p-3 text-left text-white">Būsena</th>
                            <th class="p-3 text-left text-white">Įkelti siuntos patvirtinimą</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($shipments as $s)
                        <tr class="block sm:table-row align-top @unless($loop->last) border-b @endunless" style="border-color: #836354">
                            <td class="p-3 block sm:table-cell text-black">
                                <span class="font-semibold sm:hidden">Užsakymas: </span>
                                #{{ $s->order_id }}
                            </td>

                            <td class="p-3 block sm:table-cell text-black">
                                <span class="font-semibold sm:hidden">Prekės:</span>
                                @foreach($s->order->orderItem as $item)
                                    @if($item->listing->user_id === auth()->id())
                                        <div class="flex items-center gap-3 mb-3 mt-2">
                                            <div class="w-14 h-14 bg-white rounded border flex items-center justify-center overflow-hidden shrink-0" style="border-color: #836354">
                                                <img
                                                    src="{{ $item->listing->photos->isNotEmpty()
                                                        ? \Illuminate\Support\Facades\Storage::disk('photos')->url($item->listing->photos->first()->failo_url)
                                                        : 'https://via.placeholder.com/60x60?text=No+Image'
                                                    }}"
                                                    class="max-w-full max-h-full object-contain"
                                                >
                                            </div>

                                            <div>
                                                <div class="font-medium text-black">
                                                    {{ $item->listing->pavadinimas }}
                                                </div>
                                                <div class="text-black text-xs">
                                                    × {{ $item->kiekis }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </td>

                            <td class="p-3 block sm:table-cell text-black">
                                <span class="font-semibold sm:hidden">Pristatymas: </span>
                                {{ strtoupper($s->carrier) }}
                                ({{ $s->package_size }})<br>
                                €{{ number_format($s->shipping_cents / 100, 2) }}

                                @if($s->order->address && $s->order->address->city)
                                    <div class="text-black text-xs mt-1">
                                        Pristatymas:
                                        {{ $s->order->address->city->pavadinimas }},
                                        {{ $s->order->address->city->country->pavadinimas }}
                                    </div>
                                @endif
                            </td>

                            <td class="p-3 block sm:table-cell text-black">
                                <span class="font-semibold sm:hidden">Pristatymo adresas: </span>

                                @php
                                    $shippingAddress = $s->order->shipping_address ?? [];

                                    $addressLine = trim(collect([
                                        $shippingAddress['address'] ?? null,
                                    ])->filter()->implode(' '));

                                    $cityLine = $shippingAddress['city']
                                        ?? ($s->order->address?->city?->pavadinimas ?? null);

                                    $countryLine = $shippingAddress['country']
                                        ?? ($s->order->address?->city?->country?->pavadinimas ?? null);

                                    $postalLine = $shippingAddress['postal_code'] ?? null;
                                @endphp

                                <div class="text-sm space-y-1">
                                    <div>
                                        <span class="font-medium">Adresas:</span>
                                        {{ $addressLine ?: '—' }}
                                    </div>

                                    <div>
                                        <span class="font-medium">Miestas:</span>
                                        {{ $cityLine ?: '—' }}
                                    </div>

                                    <div>
                                        <span class="font-medium">Šalis:</span>
                                        {{ $countryLine ?: '—' }}
                                    </div>

                                    <div>
                                        <span class="font-medium">Pašto kodas:</span>
                                        {{ $postalLine ?: '—' }}
                                    </div>
                                </div>
                            </td>

                            <td class="p-3 block sm:table-cell text-black">
                                <span class="font-semibold sm:hidden">Būsena: </span>
                                @php
                                    $deadline = \Carbon\Carbon::parse($s->created_at)->addDays(14);
                                    $daysLeft = now()->diffInDays($deadline, false);
                                @endphp

                                @if($s->status === 'pending')
                                    <div class="text-black">Laukiama išsiuntimo</div>

                                    @if($daysLeft >= 0)
                                        <div class="text-xs mt-1" style="color: rgb(184, 80, 54)">
                                            {{ $daysLeft }} d. liko išsiuntimui
                                        </div>
                                    @else
                                        <div class="text-xs mt-1" style="color: rgb(184, 80, 54)">
                                            Pristatymo terminas pasibaigė
                                        </div>
                                    @endif

                                @elseif($s->status === 'needs_review')
                                    <span class="font-medium" style="color: rgb(131, 99, 84)">Laukiama patvirtinimo</span>

                                @elseif($s->status === 'approved')
                                    <span style="color: rgb(184, 80, 54)">Apdorojamas kompensavimas</span>

                                @elseif($s->status === 'reimbursed')
                                    <span style="color: rgb(131, 99, 84)">Užbaigta</span>

                                @else
                                    <span class="text-black">Nežinoma</span>
                                @endif
                            </td>

                            <td class="p-3 block sm:table-cell text-black">
                                <span class="font-semibold sm:hidden">Siuntos patvirtinimas:</span>
                                @if($s->status === 'pending')
                                    @php
                                        $showShipmentErrors = old('shipment_form_id') == $s->id && $errors->any();
                                    @endphp

                                    <form method="POST"
                                          action="{{ route('seller.shipments.update', $s) }}"
                                          enctype="multipart/form-data"
                                          class="space-y-2 mt-2">
                                        @csrf
                                        <input type="hidden" name="shipment_form_id" value="{{ $s->id }}">

                                        @if($showShipmentErrors)
                                            <div class="p-3 rounded text-sm text-black border" style="background-color: rgb(207, 174, 134); border-color: #836354">
                                                <ul class="list-disc pl-5">
                                                    @foreach($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <input
                                            name="tracking_number"
                                            value="{{ old('shipment_form_id') == $s->id ? old('tracking_number') : '' }}"
                                            class="border p-2 rounded w-full text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                            style="background-color: rgb(234, 220, 200); border-color: #6B7280"
                                            placeholder="Siuntos sekimo numeris"
                                        >

                                        <input
                                            type="file"
                                            name="proof"
                                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                            class="border p-2 rounded w-full text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                            style="background-color: rgb(234, 220, 200); border-color: #6B7280"
                                        >

                                        <p class="text-xs text-black">
                                            Leidžiami failai: JPG, JPEG, PNG, PDF, DOC arba DOCX.
                                        </p>

                                        <button
                                            class="text-white px-3 py-2 rounded w-full hover:text-black"
                                            style="background-color: rgb(131, 99, 84)">
                                            Pateikti siuntą
                                        </button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-black">
                                Kol kas pardavimų nėra.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-black">
                {{ $shipments->appends(request()->except('shipments_page'))->links() }}
            </div>

            {{-- PASLAUGŲ UŽSAKYMAI --}}
<div id="paslaugu-uzsakymai" class="pt-6">
    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-black">Paslaugų užsakymai</h2>
</div>

<div class="shadow rounded overflow-hidden" style="background-color: rgb(215, 183, 142)">
    <table class="w-full text-sm text-black">
        <thead class="border-b hidden sm:table-header-group" style="background-color: rgb(131, 99, 84); border-color: #836354">
            <tr>
                <th class="p-3 text-left text-white">Užsakymas</th>
                <th class="p-3 text-left text-white">Skelbimas</th>
                <th class="p-3 text-left text-white">Pirkėjas</th>
                <th class="p-3 text-left text-white">Pristatymas</th>
                <th class="p-3 text-left text-white">Pristatymo adresas</th>
                <th class="p-3 text-left text-white">Būsena</th>
                <th class="p-3 text-left text-white">Įrodymas / veiksmai</th>
            </tr>
        </thead>

        <tbody>
        @forelse($serviceOrders as $so)
            <tr id="service-order-{{ $so->id }}" class="block sm:table-row border-b" style="border-color: #836354">
                <td class="p-3 block sm:table-cell text-black">
                    <span class="font-semibold sm:hidden">Užsakymas: </span>
                    #{{ $so->id }}
                </td>

                <td class="p-3 block sm:table-cell text-black">
                    <span class="font-semibold sm:hidden">Skelbimas: </span>
                    {{ $so->original_listing_title }}<br>
                    <span class="text-xs">€{{ number_format((float) $so->final_price, 2) }}</span>
                </td>

                <td class="p-3 block sm:table-cell text-black">
                    <span class="font-semibold sm:hidden">Pirkėjas: </span>
                    @if($so->is_anonymous)
                        Nenurodytas
                    @elseif($so->buyer)
                        {{ $so->buyer->vardas ?? $so->buyer->name }}
                    @elseif($so->buyer_code_snapshot)
                        Kodas: {{ $so->buyer_code_snapshot }}
                    @else
                        Nepriskirtas
                    @endif
                </td>

                <td class="p-3 block sm:table-cell text-black">
                    <span class="font-semibold sm:hidden">Pristatymas: </span>

                    @if($so->carrier || $so->package_size || !is_null($so->shipping_cents))
                        <div class="text-sm space-y-1">
                            <div>
                                <span class="font-medium">Vežėjas:</span>
                                {{ $so->carrier ? strtoupper($so->carrier) : '—' }}
                            </div>

                            <div>
                                <span class="font-medium">Pakuotės dydis:</span>
                                {{ $so->package_size ?: '—' }}
                            </div>

                            <div>
                                <span class="font-medium">Kaina:</span>
                                @if(!is_null($so->shipping_cents))
                                    €{{ number_format($so->shipping_cents / 100, 2) }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    @else
                        —
                    @endif
                </td>

                <td class="p-3 block sm:table-cell text-black">
                    <span class="font-semibold sm:hidden">Pristatymo adresas: </span>

                    @php
                        $serviceShippingAddress = $so->convertedOrder?->shipping_address ?? [];

                        $serviceAddressLine = trim(collect([
                            $serviceShippingAddress['address'] ?? null,
                        ])->filter()->implode(' '));

                        $serviceCityLine = $serviceShippingAddress['city']
                            ?? ($so->convertedOrder?->address?->city?->pavadinimas ?? null);

                        $serviceCountryLine = $serviceShippingAddress['country']
                            ?? ($so->convertedOrder?->address?->city?->country?->pavadinimas ?? null);

                        $servicePostalLine = $serviceShippingAddress['postal_code'] ?? null;
                    @endphp

                    <div class="text-sm space-y-1">
                        <div>
                            <span class="font-medium">Adresas:</span>
                            {{ $serviceAddressLine ?: '—' }}
                        </div>

                        <div>
                            <span class="font-medium">Miestas:</span>
                            {{ $serviceCityLine ?: '—' }}
                        </div>

                        <div>
                            <span class="font-medium">Šalis:</span>
                            {{ $serviceCountryLine ?: '—' }}
                        </div>

                        <div>
                            <span class="font-medium">Pašto kodas:</span>
                            {{ $servicePostalLine ?: '—' }}
                        </div>
                    </div>
                </td>

                <td class="p-3 block sm:table-cell text-black">
                    <span class="font-semibold sm:hidden">Būsena: </span>

                    @if($so->status === \App\Models\ServiceOrder::STATUS_COMPLETED)
                        <span style="color: rgb(131, 99, 84)">Užbaigta</span>
                        @if($so->completion_method === \App\Models\ServiceOrder::COMPLETION_PRIVATE)
                            <div class="text-xs mt-1 text-black">Užbaigta privačiai</div>
                        @elseif($so->completion_method === \App\Models\ServiceOrder::COMPLETION_PLATFORM)
                            <div class="text-xs mt-1 text-black">Užbaigta per svetainę</div>
                        @endif

                    @elseif($so->shipment_status === \App\Models\ServiceOrder::SHIPMENT_NEEDS_REVIEW)
                        <span class="font-medium" style="color: rgb(131, 99, 84)">Laukiama patvirtinimo</span>

                    @elseif($so->shipment_status === \App\Models\ServiceOrder::SHIPMENT_APPROVED)
                        <span style="color: rgb(184, 80, 54)">Apdorojamas kompensavimas</span>

                    @elseif($so->shipment_status === \App\Models\ServiceOrder::SHIPMENT_REIMBURSED)
                        <span style="color: rgb(131, 99, 84)">Užbaigta</span>

                    @elseif($so->status === \App\Models\ServiceOrder::STATUS_READY_TO_SHIP)
                        @if($so->payment_status === \App\Models\ServiceOrder::PAYMENT_PAID)
                            <div class="text-black">Laukiama išsiuntimo</div>
                        @else
                            <span class="font-medium" style="color: rgb(131, 99, 84)">Laukiama pirkėjo apmokėjimo</span>
                        @endif

                    @else
                        <span class="text-black">{{ $so->lithuanian_status }}</span>
                    @endif
                </td>

                <td class="p-3 block sm:table-cell text-black">
                    <span class="font-semibold sm:hidden">Veiksmai: </span>

                    @if($so->status === \App\Models\ServiceOrder::STATUS_READY_TO_SHIP)
                        @if($so->completion_method === null)
                            <div class="space-y-2 mt-2">
                                <form method="POST" action="{{ route('seller.service-orders.choose-platform', $so) }}">
                                    @csrf
                                    <button
                                        class="text-white px-3 py-2 rounded w-full hover:text-black"
                                        style="background-color: rgb(131, 99, 84)">
                                        Atsiskaitymas per svetainę
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('seller.service-orders.choose-private', $so) }}">
                                    @csrf
                                    <button
                                        class="text-white px-3 py-2 rounded w-full"
                                        style="background-color: rgb(184, 80, 54)">
                                        Užbaigti privačiai
                                    </button>
                                </form>
                            </div>

                        @elseif(
                            $so->completion_method === \App\Models\ServiceOrder::COMPLETION_PLATFORM &&
                            $so->payment_status !== \App\Models\ServiceOrder::PAYMENT_PAID
                        )
                            <div class="text-sm text-black">
                                Laukiama, kol pirkėjas apmokės per svetainę.
                            </div>

                            <form method="POST" action="{{ route('seller.service-orders.choose-private', $so) }}" class="mt-2">
                                @csrf
                                <button
                                    class="text-white px-3 py-2 rounded w-full"
                                    style="background-color: rgb(184, 80, 54)">
                                    Perjungti į privatų
                                </button>
                            </form>

                        @elseif(
                            $so->completion_method === \App\Models\ServiceOrder::COMPLETION_PLATFORM &&
                            $so->payment_status === \App\Models\ServiceOrder::PAYMENT_PAID &&
                            $so->shipment_status === \App\Models\ServiceOrder::SHIPMENT_PENDING
                        )
                            @php
                                $showServiceShipmentErrors = old('service_order_form_id') == $so->id && $errors->any();
                            @endphp

                            <form method="POST"
                                  action="{{ route('seller.service-orders.shipment.submit', $so) }}"
                                  enctype="multipart/form-data"
                                  class="space-y-2 mt-2">
                                @csrf
                                <input type="hidden" name="service_order_form_id" value="{{ $so->id }}">

                                @if($showServiceShipmentErrors)
                                    <div class="p-3 rounded text-sm text-black border" style="background-color: rgb(207, 174, 134); border-color: #836354">
                                        <ul class="list-disc pl-5">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <input
                                    name="tracking_number"
                                    value="{{ old('service_order_form_id') == $so->id ? old('tracking_number') : '' }}"
                                    class="border p-2 rounded w-full text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                    style="background-color: rgb(234, 220, 200); border-color: #6B7280"
                                    placeholder="Siuntos sekimo numeris"
                                >

                                <input
                                    type="file"
                                    name="proof"
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                    class="border p-2 rounded w-full text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                    style="background-color: rgb(234, 220, 200); border-color: #6B7280"
                                >

                                <p class="text-xs text-black">
                                    Leidžiami failai: JPG, JPEG, PNG, PDF, DOC arba DOCX.
                                </p>

                                <button
                                    class="text-white px-3 py-2 rounded w-full hover:text-black"
                                    style="background-color: rgb(131, 99, 84)">
                                    Pateikti siuntos įrodymą
                                </button>
                            </form>

                        @elseif($so->shipment_status === \App\Models\ServiceOrder::SHIPMENT_NEEDS_REVIEW && $so->proof_path)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($so->proof_path) }}"
                               target="_blank"
                               class="underline"
                               style="color: rgb(131, 99, 84)">
                                Peržiūrėti įrodymą
                            </a>

                        @else
                            —
                        @endif
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="p-4 text-center text-black">
                    Paslaugų užsakymų šiame skyriuje nėra.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 text-black">
    {{ $serviceOrders->appends(request()->except('service_orders_page'))->fragment('paslaugu-uzsakymai')->links() }}
</div>

        </div>
    </div>
    @include('components.footer')
</x-app-layout>
