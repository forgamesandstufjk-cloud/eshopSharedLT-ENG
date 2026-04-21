<x-app-layout>
     <x-slot name="head">
        <title>Mano skelbimai</title>
        <meta name="description" content="Peržiūrėkite, redaguokite ir tvarkykite savo paskelbtus skelbimus." />
    </x-slot>
    <div class="min-h-screen flex flex-col" style="background-color: rgb(234, 220, 200)">
        <div class="relative flex-1 w-full px-3 sm:px-4 mt-6 sm:mt-8 pb-10">
            <div
                class="fixed inset-0 pointer-events-none bg-no-repeat bg-center bg-contain z-0"
                style="background-image: url('{{ asset('images/vytis.png') }}'); background-size: 500px 500px; background-position: center calc(50% + 60px); opacity: 0.3"
            ></div>

            <div class="container mx-auto relative z-10">

                <h1 class="text-3xl font-bold mb-6">Mano skelbimai</h1>

                @if($listings->isEmpty())
                    <p class="text-black">Jūs dar nesate paskelbę jokių skelbimų.</p>
                @endif

                <div
                    class="grid grid-cols-[repeat(auto-fit,minmax(260px,320px))] gap-4 sm:gap-6 justify-center"
                    x-data="myListingsComponent({{ Js::from($listings->items()) }})"
                >

                    <template x-for="item in listings" :key="item.id">
                        <div
                            class="shadow rounded overflow-hidden hover:shadow-lg transition flex flex-col"
                            style="background-color: rgb(215, 183, 142)"
                        >
                            <!-- IMAGE -->
                            <a :href="'/listing/' + item.id" class="block">
                                <div class="w-full h-56 sm:h-64 bg-white flex items-center justify-center overflow-hidden">
                                    <img
                                        :src="item.photos?.[0]
                                            ? @js(\Illuminate\Support\Facades\Storage::disk('photos')->url('')) + item.photos[0].failo_url
                                            : 'https://via.placeholder.com/300'"
                                        class="max-w-full max-h-full object-contain"
                                    />
                                </div>
                            </a>

                            <div class="p-3 sm:p-4 flex flex-col flex-1 justify-end">
                                <!-- TITLE -->
                                <a :href="'/listing/' + item.id" class="block">
                                    <h2 class="text-base sm:text-lg font-semibold mb-1 leading-snug break-words whitespace-normal line-clamp-1"
                                        x-text="item.pavadinimas"></h2>
                                </a>

                                <!-- DESCRIPTION -->
                                <a :href="'/listing/' + item.id" class="block text-black hover:underline">
                                    <p class="text-black text-sm break-words whitespace-normal line-clamp-1"
                                       x-text="item.aprasymas"></p>
                                </a>

                                <!-- PRICE -->
                                <div class="flex justify-between items-center pt-3">
                                    <span class="font-bold text-base sm:text-lg"
                                          style="color: rgb(131, 99, 84)"
                                          x-text="item.kaina + ' €'"></span>
                                </div>

                                <!-- STOCK -->
                                <div class="mt-2 text-sm">
                                    <template x-if="item.tipas === 'preke'">
                                        <div>
                                            <strong>Kiekis:</strong>
                                            <span
                                                :class="item.kiekis == 0 ? 'text-red-600 font-bold' : ''"
                                                x-text="item.kiekis"
                                            ></span>
                                        </div>
                                    </template>
                                </div>

                                <!-- ACTIONS -->
                                <div class="flex justify-between items-center mt-4">
                                    <a
                                        :href="'/listing/' + item.id + '/edit'"
                                        class="text-white font-semibold px-4 py-2 rounded hover:text-black"
                                        style="background-color: rgb(131, 99, 84)"
                                    >
                                        Redaguoti
                                    </a>

                                    <button
                                        @click="deleteListing(item.id)"
                                        class="text-white font-semibold px-4 py-2 rounded hover:text-black"
                                        style="background-color: rgb(184, 80, 54)"
                                    >
                                        Ištrinti
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>

                @if(method_exists($listings, 'links'))
                    <div class="mt-8 mb-2 flex justify-center relative z-10">
                        {{ $listings->links() }}
                    </div>
                @endif

            </div>
        </div>

        <div class="mt-6">
            @include('components.footer')
        </div>
    </div>

    <script>
        function myListingsComponent(initialListings) {
            return {
                listings: initialListings,

                getCSRFToken() {
                    return document.cookie
                        .split('; ')
                        .find(row => row.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1];
                },

                deleteListing(id) {
                    if (!confirm('Ar tikrai norite ištrinti šį skelbimą?')) return;

                    const token = this.getCSRFToken();

                    fetch('/api/listing/' + id, {
                        method: 'DELETE',
                        credentials: 'include',
                        headers: {
                            'Accept': 'application/json',
                            'X-XSRF-TOKEN': decodeURIComponent(token)
                        }
                    })
                    .then(res => res.json())
                    .then(() => {
                        this.listings = this.listings.filter(l => l.id !== id);
                    })
                    .catch(err => console.error('Delete failed:', err));
                }
            }
        }
    </script>
</x-app-layout>
