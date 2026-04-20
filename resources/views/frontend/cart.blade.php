<x-app-layout>

<div class="w-full max-w-4xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0 pb-10" style="background-color: rgb(234, 220, 200)">
    <div class="container mx-auto relative z-10">
        
        <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6 text-black">Mano krepšelis</h1>

      

        @if($cartItems->isEmpty())
            <div class="shadow p-6 rounded text-center" style="background-color: rgb(215, 183, 142)">
                <p class="text-black">Jūsų krepšelis yra tuščias.</p>
            </div>
        @else

            {{-- CLEAR CART BUTTON --}}
            <form action="{{ route('cart.clear') }}" method="POST"
                  onsubmit="return confirm('Ar tikrai norite išvalyti visą krepšelį?')">
                @csrf
                @method('DELETE')

                <button class="mb-4 text-white px-4 py-2 rounded hover:text-black w-full sm:w-auto"
                        style="background-color: rgb(184, 80, 54)">
                    Išvalyti krepšelį
                </button>
            </form>

            @php
    $cartData = $cartItems->map(function ($item) {
        return [
            'id' => $item->id,
            'listing_id' => $item->listing_id,
            'pavadinimas' => $item->listing->pavadinimas,
            'aprasymas' => $item->listing->aprasymas,
            'kaina' => (float) $item->listing->kaina,
            'kiekis' => (int) $item->kiekis,
            'max' => (int) $item->listing->kiekis,
            'photo' => $item->listing->photos->isNotEmpty()
            ? \Illuminate\Support\Facades\Storage::disk('photos')->url($item->listing->photos->first()->failo_url)
            : 'https://via.placeholder.com/150',
            'url' => route('listing.single', $item->listing_id),
        ];
    })->values();
@endphp

            <div x-data="cartPage(@js($cartData), '{{ csrf_token() }}')">
                {{-- CART ITEMS --}}
                <div class="shadow rounded p-3 sm:p-4" style="background-color: rgb(215, 183, 142)">

                    {{-- HEADER --}}
                    <div class="hidden sm:grid grid-cols-12 font-semibold text-black border-b pb-2 mb-4"
                         style="border-color: #836354">
                        <div class="col-span-6 px-2">Prekė</div>
                        <div class="col-span-2 text-right px-2">Kaina</div>
                        <div class="col-span-2 text-center px-2">Kiekis</div>
                    </div>

                    <template x-for="item in items" :key="item.id">
                        <div class="border-b py-4" style="border-color: #836354;">
                            <div class="flex flex-col sm:grid sm:grid-cols-12 sm:items-center gap-3 sm:gap-0 text-black">

                                {{-- IMAGE + TITLE --}}
                                <div class="sm:col-span-6 flex items-center gap-4 justify-center sm:justify-start">
                                   <div :href="item.url"
                                        class="w-20 h-20 sm:w-24 sm:h-24 bg-white rounded flex items-center justify-center overflow-hidden shrink-0">
                                    <img
                                        :src="item.photo"
                                        class="max-w-full max-h-full object-contain"
                                    >
                                    </div>
                                        <div class="min-w-0 flex-1 text-center sm:text-left">
                                        <a :href="item.url"
                                           class="font-semibold text-black hover:underline block truncate"
                                           x-text="item.pavadinimas">
                                        </a>
                                    
                                        <a :href="item.url"
                                            class="text-sm text-black/70 line-clamp-2" 
                                            x-text="item.aprasymas || ''"></a>
                                    </div>
                                </div>

                                {{-- PRICE --}}
                                <div class="sm:col-span-2 text-center sm:text-right font-semibold text-black"
                                     x-text="formatPrice(item.kaina)">
                                </div>

                                {{-- QUANTITY --}}
                                <div class="sm:col-span-2 flex justify-center items-center">
                                    <button
                                        type="button"
                                        @click="decrease(item)"
                                        class="px-3 py-1 rounded text-white hover:text-black"
                                        :class="item.kiekis <= 1 || item.loading ? 'opacity-50 cursor-not-allowed' : ''"
                                        style="background-color: rgb(131, 99, 84)"
                                        :disabled="item.kiekis <= 1 || item.loading"
                                    >
                                        −
                                    </button>

                                    <span class="px-4 font-semibold text-black" x-text="item.kiekis"></span>

                                   <button
                                         type="button"
                                         @click="increase(item)"
                                        class="px-3 py-1 rounded text-white hover:text-black"
                                        :class="item.loading || item.kiekis >= item.max ? 'opacity-50 cursor-not-allowed' : ''"
                                        style="background-color: rgb(131, 99, 84)"
                                        :disabled="item.loading || item.kiekis >= item.max"
                                        >
                                            +
                                    </button>
                                </div>

                                {{-- REMOVE --}}
                                <div class="sm:col-span-2 flex justify-center sm:justify-end">
                                    <form method="POST"
                                         :action="`/cart/remove/${item.id}`"
                                          class="sm:col-span-2 flex justify-center sm:justify-end"
                                          onsubmit="return confirm('Ar tikrai norite pašalinti šią prekę iš krepšelio?')">
                                            @csrf
                                            @method('DELETE')
                                    <button class="text-sm sm:text-xl text-black hover:underline"
                                    style="color: rgb(184, 80, 54)">
                                        Pašalinti
                                    </button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </template>

                    <template x-if="items.length === 0">
                        <div class="p-6 text-center text-black">
                            Jūsų krepšelis yra tuščias.
                        </div>
                    </template>
                </div>

                {{-- TOTAL SECTION --}}
                <div class="shadow rounded p-4 sm:p-6 mt-6" style="background-color: rgb(215, 183, 142)">
                    <div class="text-lg sm:text-xl font-bold mb-4 text-center sm:text-left text-black">
                        Viso: <span x-text="formatPrice(total)"></span>
                    </div>

                    {{-- CHECKOUT --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <a
        href="{{ session('continue_shopping_url', route('home')) }}"
        class="text-white px-6 py-3 rounded hover:text-black w-full text-center"
        style="background-color: rgb(184, 80, 54)">
        Tęsti apsipirkimą
    </a>

    <form method="GET" action="{{ route('checkout.index') }}">
        <button
            type="submit"
            class="text-white px-6 py-3 rounded hover:text-black w-full"
            style="background-color: rgb(131, 99, 84)"
        >
            Eiti į atsiskaitymą
        </button>
    </form>
</div>
                </div>
            </div>
        @endif
    </div>
</div>
  @include('components.footer')
<script>
    function cartPage(initialItems, csrf) {
        return {
            items: initialItems.map(item => ({ ...item, loading: false })),
            csrf,

            formatPrice(value) {
                return Number(value).toFixed(2) + " €";
            },

            get total() {
                return this.items.reduce((sum, item) => sum + (item.kaina * item.kiekis), 0);
            },

            async post(url, method = "POST") {
                const response = await fetch(url, {
                    method,
                    headers: {
                        "X-CSRF-TOKEN": this.csrf,
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    credentials: "same-origin"
                });

                if (!response.ok) {
                    throw new Error("Request failed");
                }

                return response;
            },

            async decrease(item) {
                if (item.kiekis <= 1 || item.loading) return;

                const oldQty = item.kiekis;
                item.loading = true;
                item.kiekis--;

                try {
                    await this.post(`/cart/decrease/${item.id}`);
                } catch (e) {
                    item.kiekis = oldQty;
                    alert("Nepavyko atnaujinti krepšelio.");
                } finally {
                    item.loading = false;
                }
            },

            async increase(item) {
    if (item.loading || item.kiekis >= item.max) return;

    const oldQty = item.kiekis;
    item.loading = true;
    item.kiekis++;

    try {
        await this.post(`/cart/increase/${item.id}`);
    } catch (e) {
        item.kiekis = oldQty;
        alert("Nepavyko atnaujinti krepšelio.");
    } finally {
        item.loading = false;
    }
          }   
     };
}
</script>
</x-app-layout>
