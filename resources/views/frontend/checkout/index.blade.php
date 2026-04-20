<x-app-layout>
    <meta name="stripe-key" content="{{ config('services.stripe.key') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="service-order-id" content="{{ $serviceOrder?->id ?? '' }}">

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="max-w-5xl mx-auto mt-10 pb-10" style="background-color: rgb(234, 220, 200)">
        <h1 class="text-3xl font-bold mb-6 ml-2 sm:ml-0 text-black">Atsiskaitymas</h1>

        <div class="grid md:grid-cols-2 gap-6">

            {{-- LEFT: SHIPPING + PAYMENT --}}
            <div class="p-6 rounded shadow" style="background-color: rgb(215, 183, 142)">
                <form id="checkout-form">

                    <h2 class="font-semibold mb-3 text-black">Pristatymo adresas</h2>

                    <div class="space-y-4">

                        {{-- Address --}}
                        <div>
                            <label class="block text-sm font-medium text-black mb-1">
                                Adresas
                            </label>
                            <input
                                type="text"
                                name="address"
                                value="{{ old('address',
                                    $user->address
                                        ? trim(collect([
                                            $user->address->gatve,
                                            $user->address->namo_nr,
                                            $user->address->buto_nr ? 'Flat '.$user->address->buto_nr : null,
                                        ])->filter()->implode(' '))
                                        : ''
                                ) }}"
                                class="w-full border border-gray-500 rounded px-3 py-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                style="background-color: rgb(234, 220, 200)"
                                required
                            >
                        </div>

                        {{-- City --}}
                        <div>
                            <label class="block text-sm font-medium text-black mb-1">
                                Miestas
                            </label>
                            <input
                                type="text"
                                name="city"
                                value="{{ old('city', $user->address->city->pavadinimas ?? '') }}"
                                class="w-full border border-gray-500 rounded px-3 py-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                style="background-color: rgb(234, 220, 200)"
                                required
                            >
                        </div>

                        {{-- Country --}}
                        <div>
                            <label class="block text-sm font-medium text-black mb-1">
                                Šalis
                            </label>
                            <input
                                type="text"
                                name="country"
                                value="{{ old('country', 'Lithuania') }}"
                                class="w-full border border-gray-500 rounded px-3 py-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                style="background-color: rgb(234, 220, 200)"
                                required
                            >
                        </div>

                        {{-- Postal code --}}
                        <div>
                            <label class="block text-sm font-medium text-black mb-1">
                                Pašto kodas
                            </label>
                            <input
                                id="postal_code"
                                name="postal_code"
                                class="w-full border border-gray-500 rounded px-3 py-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                style="background-color: rgb(234, 220, 200)"
                                required
                            >
                        </div>

                    </div>

                    <br>

                    <h2 class="font-semibold mb-2 text-black">
                        {{ $checkoutMode === 'service' ? 'Pristatymo būdas' : 'Pristatymo būdas' }}
                    </h2>

                    <div class="mb-4 relative" x-data="{ open: false, selected: '' }">
                        <input type="hidden" id="shipping-carrier" name="shipping_carrier" :value="selected">

                        <button
                            type="button"
                            @click="open = !open"
                            :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                            class="w-full rounded border py-2 px-3 text-left text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                            style="background-color: rgb(234, 220, 200)"
                        >
                            <span x-text="selected === '' ? 'Pasirinkite pristatymo būdą' : (selected === 'omniva' ? 'Omniva (paštomatas)' : 'Venipak (kurjeris)')"></span>

                            <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            @click.outside="open = false"
                            class="absolute left-0 right-0 top-full mt-1 rounded border shadow overflow-hidden z-50"
                            style="background-color: rgb(234, 220, 200); border-color: #836354"
                        >

                            <div
                                @click="
                                    selected = 'omniva';
                                    open = false;
                                    $nextTick(() => {
                                        const input = document.getElementById('shipping-carrier');
                                        input.dispatchEvent(new Event('input', { bubbles: true }));
                                        input.dispatchEvent(new Event('change', { bubbles: true }));
                                    });
                                "
                                class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                            >
                                Omniva (paštomatas)
                            </div>

                            <div
                                @click="
                                    selected = 'venipak';
                                    open = false;
                                    $nextTick(() => {
                                        const input = document.getElementById('shipping-carrier');
                                        input.dispatchEvent(new Event('input', { bubbles: true }));
                                        input.dispatchEvent(new Event('change', { bubbles: true }));
                                    });
                                "
                                class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                            >
                                Venipak (kurjeris)
                            </div>
                        </div>
                    </div>

                    @if($checkoutMode === 'service')
                        <p class="text-sm text-black mb-4">
                            Pasirinkite pristatymo būdą. Siuntos dydį jau nustatė pardavėjas.
                        </p>
                    @else
                        <p class="text-sm text-black mb-4">
                            Kiekvienas pardavėjas siunčia atskirai.
                        </p>
                    @endif

                    <h2 class="font-semibold mb-2 text-black">Mokėjimas</h2>

                    <div id="payment-element" class="border border-gray-500 p-4 rounded mb-4"
                         style="background-color: rgb(234, 220, 200)"></div>

                    <div id="checkout-error"
                         class="hidden p-3 mb-3 rounded text-black"
                         style="background-color: rgb(207, 174, 134)">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <a
                            href="{{ $checkoutMode === 'service' ? route('buyer.orders') : route('cart.index') }}"
                            class="text-white py-3 rounded font-semibold hover:text-black text-center"
                            style="background-color: rgb(184, 80, 54)"
                        >
                            {{ $checkoutMode === 'service' ? 'Grįžti į pirkimus' : 'Grįžti į krepšelį' }}
                        </a>

                        <button
                            id="pay-button"
                            type="submit"
                            class="text-white py-3 rounded font-semibold hover:text-black"
                            style="background-color: rgb(131, 99, 84)"
                        >
                            Mokėti
                        </button>
                    </div>
                </form>
            </div>

            {{-- RIGHT: ORDER SUMMARY --}}
            <div class="p-6 rounded shadow" style="background-color: rgb(215, 183, 142)">
                <h2 class="text-xl font-semibold mb-4 text-black">Užsakymo suvestinė</h2>

                @if($checkoutMode === 'service')
                    <div class="mb-3 text-black">
                        <div class="flex justify-between">
                            <span>{{ $serviceOrder->original_listing_title }}</span>
                            <span>
                                {{ number_format((float) $serviceOrder->final_price, 2) }} €
                            </span>
                        </div>
                        <div class="text-sm text-black">
                            Pardavėjas: {{ $serviceOrder->seller->vardas }}
                        </div>
                        <div class="text-sm text-black">
                            Siuntos dydis: {{ $serviceOrder->package_size ?: '—' }}
                        </div>
                    </div>
                @else
                    @foreach($cartItems as $item)
                        <div class="mb-3 text-black">
                            <div class="flex justify-between">
                                <span>{{ $item->listing->pavadinimas }}</span>
                                <span>
                                    {{ number_format($item->listing->kaina * $item->kiekis, 2) }} €
                                </span>
                            </div>
                            <div class="text-sm text-black">
                                Pardavėjas: {{ $item->listing->user->vardas }}
                            </div>
                        </div>
                    @endforeach
                @endif

                <hr class="my-3" style="border-color: #836354">

                <div class="flex justify-between text-sm text-black">
                    <span>Prekių suma</span>
                    <span id="items-total">—</span>
                </div>

                <div id="small-order-row"
                     class="flex justify-between text-sm hidden"
                     style="color: rgb(184, 80, 54)">
                    <span>Mažo užsakymo mokestis</span>
                    <span id="small-order-fee">—</span>
                </div>

                <div class="flex justify-between text-sm text-black">
                    <span>Pristatymas</span>
                    <span id="shipping-total">—</span>
                </div>

                <hr class="my-3" style="border-color: #836354">

                <div class="flex justify-between font-semibold text-lg text-black">
                    <span>Iš viso</span>
                    <span id="order-total">—</span>
                </div>
            </div>
        </div>
    </div>
         @include('components.footer')
    @vite('resources/js/checkout.js')
</x-app-layout>
