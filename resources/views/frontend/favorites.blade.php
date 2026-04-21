<x-app-layout>
    <x-slot name="title">Mano mėgstamiausi</x-slot>
    
    @php
        $cartCounts = auth()->check()
            ? \App\Models\Cart::where('user_id', auth()->id())
                ->get()
                ->pluck('kiekis', 'listing_id')
                ->toArray()
            : [];
    @endphp

    <div class="min-h-screen flex flex-col" style="background-color: rgb(234, 220, 200)">
        <div
            x-data="{
                listings: [],
                loading: true,
                currentPage: 1,
                perPage: 12,
                cartCounts: {{ \Illuminate\Support\Js::from($cartCounts) }},

                async load() {
                    try {
                        const res = await fetch('/api/favorites/my', {
                            credentials: 'include',
                            headers: { Accept: 'application/json' },
                        });

                        this.listings = res.ok ? await res.json() : [];
                        this.currentPage = 1;

                    } catch (e) {
                        console.error('Failed loading favorites', e);
                        this.listings = [];
                    } finally {
                        this.loading = false;
                    }
                },

                get totalPages() {
                    return Math.max(1, Math.ceil(this.listings.length / this.perPage));
                },

                get paginatedListings() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.listings.slice(start, start + this.perPage);
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                remainingToAdd(item) {
                    const alreadyInCart = Number(this.cartCounts[item.id] ?? 0);
                    const stock = Number(item.kiekis ?? 0);
                    return Math.max(0, stock - alreadyInCart);
                }
            }"
            x-init="load()"
            class="flex-1 w-full px-4 mt-10 relative pb-10"
        >
            <div
                class="fixed inset-0 pointer-events-none bg-no-repeat z-0"
                style="background-image: url('{{ asset('images/vytis.png') }}'); background-size: 500px 500px; background-position: center calc(50% + 40px); opacity: 0.3"
            ></div>

            <div class="container mx-auto relative z-10">
                <h1 class="text-3xl font-bold mb-6">Išsaugoti</h1>

                <template x-if="loading">
                    <p class="text-gray-500">Kraunami mėgstamiausi…</p>
                </template>

                <template x-if="!loading && listings.length === 0">
                    <p class="text-gray-600">Neturite mėgstamų skelbimų.</p>
                </template>

                <div
                    x-show="!loading && listings.length > 0"
                    class="grid grid-cols-[repeat(auto-fit,minmax(260px,320px))] gap-4 sm:gap-6 justify-center"
                >
                    <template x-for="item in paginatedListings" :key="item.id">
                        <div
                            class="shadow rounded overflow-hidden hover:shadow-lg transition flex flex-col h-full cursor-pointer"
                            style="background-color: rgb(215, 183, 142)"
                            @click="window.location.href = '/listing/' + item.id"
                        >
                            <div class="relative">
                                <div class="w-full h-56 sm:h-64 bg-white flex items-center justify-center overflow-hidden">
                                    <img
                                        :src="item.photos?.length
                                            ? item.photos[0].url
                                            : 'https://via.placeholder.com/400x350?text=No+Image'"
                                            :alt="item.pavadinimas ? item.pavadinimas + ' nuotrauka' : 'Skelbimo nuotrauka'"
                                        class="max-w-full max-h-full object-contain"
                                    >
                                </div>

                                <button
                                    x-on:click.stop.prevent="
                                        Alpine.store('favorites').toggle(item.id);
                                        load();
                                    "
                                    class="absolute top-2 right-2 z-30 w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center overflow-hidden"
                                    title="Pašalinti iš mėgstamiausių"
                                >
                                    <span class="text-2xl leading-none" style="color: rgb(131, 99, 84);">🤎</span>
                                </button>
                            </div>

                            <div class="p-4 flex flex-col flex-1">
                                <h2
                                    class="font-semibold break-words whitespace-normal line-clamp-1"
                                    x-text="item.pavadinimas"
                                ></h2>

                                <p
                                    class="text-black text-sm break-words whitespace-normal line-clamp-1 mt-1 hover:underline"
                                    x-text="item.aprasymas"
                                ></p>

                                <div class="mt-auto pt-3 flex items-center justify-between gap-2">
                                    <span
                                        class="font-bold"
                                        style="color: rgb(131, 99, 84)"
                                        x-text="item.kaina + ' €'"
                                    ></span>

                                    <div class="flex items-center gap-2">
                                        <template x-if="item.tipas === 'paslauga'">
                                            <span
                                                class="px-2 py-1 rounded text-xs text-black"
                                                style="background-color: rgb(207, 174, 134)"
                                                x-on:click.stop
                                            >
                                                Paslauga
                                            </span>
                                        </template>

                                        <template x-if="item.tipas !== 'paslauga' && item.statusas !== 'parduotas' && !item.is_hidden && item.user_id !== {{ auth()->id() ?? 'null' }} && remainingToAdd(item) > 0">
                                            <form :action="'/cart/add/' + item.id" method="POST" x-on:click.stop>
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="p-2 rounded text-black hover:text-white transition"
                                                    aria-label="Pridėti į krepšelį"
                                                    title="Pridėti į krepšelį"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor"
                                                         stroke-width="1.8"
                                                         class="w-6 h-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437m0 0L6.75 11.25m-1.644-5.977h13.239c.917 0 1.593.862 1.375 1.752l-1.273 5.25a1.125 1.125 0 01-1.094.86H6.75m-1.644-7.837L6.75 11.25m0 0L5.94 14.49a1.125 1.125 0 001.09 1.385h10.72M9 19.5a.75.75 0 100 1.5.75.75 0 000-1.5zm9 0a.75.75 0 100 1.5.75.75 0 000-1.5z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </template>

                                        <template x-if="item.tipas !== 'paslauga' && item.statusas !== 'parduotas' && !item.is_hidden && item.user_id !== {{ auth()->id() ?? 'null' }} && remainingToAdd(item) <= 0">
                                            <button
                                                type="button"
                                                disabled
                                                class="p-2 rounded text-[#eadcc8] cursor-not-allowed"
                                                aria-label="Maksimalus kiekis jau krepšelyje"
                                                title="Maksimalus kiekis jau krepšelyje"
                                                x-on:click.stop
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor"
                                                     stroke-width="1.8"
                                                     class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437m0 0L6.75 11.25m-1.644-5.977h13.239c.917 0 1.593.862 1.375 1.752l-1.273 5.25a1.125 1.125 0 01-1.094.86H6.75m-1.644-7.837L6.75 11.25m0 0L5.94 14.49a1.125 1.125 0 001.09 1.385h10.72M9 19.5a.75.75 0 100 1.5.75.75 0 000-1.5zm9 0a.75.75 0 100 1.5.75.75 0 000-1.5z" />
                                                </svg>
                                            </button>
                                        </template>

                                        <template x-if="item.tipas !== 'paslauga' && (item.statusas === 'parduotas' || item.is_hidden)">
                                            <button
                                                type="button"
                                                disabled
                                                class="p-2 rounded text-gray-400 cursor-not-allowed"
                                                aria-label="Nepasiekiama"
                                                title="Nepasiekiama"
                                                x-on:click.stop
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor"
                                                     stroke-width="1.8"
                                                     class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437m0 0L6.75 11.25m-1.644-5.977h13.239c.917 0 1.593.862 1.375 1.752l-1.273 5.25a1.125 1.125 0 01-1.094.86H6.75m-1.644-7.837L6.75 11.25m0 0L5.94 14.49a1.125 1.125 0 001.09 1.385h10.72M9 19.5a.75.75 0 100 1.5.75.75 0 000-1.5zm9 0a.75.75 0 100 1.5.75.75 0 000-1.5z" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div
                    x-show="!loading && listings.length > 0 && totalPages > 1"
                    class="mt-8 mb-2 flex flex-col items-center gap-3 relative z-10"
                >
                    <div class="text-sm text-black">
                        Rodoma
                        <span x-text="((currentPage - 1) * perPage) + 1"></span>
                        –
                        <span x-text="Math.min(currentPage * perPage, listings.length)"></span>
                        iš
                        <span x-text="listings.length"></span>
                    </div>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            @click="prevPage()"
                            :disabled="currentPage === 1"
                            class="px-4 py-2 rounded border text-sm"
                            :class="currentPage === 1 ? 'text-gray-500 cursor-not-allowed' : 'text-black'"
                            style="background-color: rgb(215, 183, 142); border-color: #836354"
                        >
                            ‹
                        </button>

                        <template x-for="page in totalPages" :key="page">
                            <button
                                type="button"
                                @click="currentPage = page; window.scrollTo({ top: 0, behavior: 'smooth' })"
                                class="px-4 py-2 rounded border text-sm"
                                :class="page === currentPage ? 'text-white font-semibold' : 'text-black'"
                                :style="page === currentPage
                                    ? 'background-color: rgb(131, 99, 84); border-color: #836354'
                                    : 'background-color: rgb(215, 183, 142); border-color: #836354'"
                                x-text="page"
                            ></button>
                        </template>

                        <button
                            type="button"
                            @click="nextPage()"
                            :disabled="currentPage === totalPages"
                            class="px-4 py-2 rounded border text-sm"
                            :class="currentPage === totalPages ? 'text-gray-500 cursor-not-allowed' : 'text-black'"
                            style="background-color: rgb(215, 183, 142); border-color: #836354"
                        >
                            ›
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            @include('components.footer')
        </div>
    </div>
</x-app-layout>
