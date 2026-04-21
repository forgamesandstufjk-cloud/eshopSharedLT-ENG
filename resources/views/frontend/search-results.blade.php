<x-app-layout>
    <x-slot name="head">
        <title>Skelbimų paieška | Prekės ir paslaugos</title>
        <meta name="description" content="Naršykite prekes ir paslaugas pagal kategoriją, kainą, miestą ir kitus filtrus. Greitai raskite tai, ko ieškote.">
    </x-slot>
    <div class="min-h-screen flex flex-col" style="background-color: rgb(234, 220, 200)">
        <div x-data
            x-init="if ({{ auth()->check() ? 'true' : 'false' }}) Alpine.store('favorites').load()"
            class="relative flex-1 w-full px-3 sm:px-4 mt-6 sm:mt-10 pb-10">
            <div
                class="fixed inset-0 pointer-events-none bg-no-repeat bg-center bg-contain"
                style="background-image: url('{{ asset('images/vytis.png') }}'); background-size: 500px 500px; background-position: center calc(50% + 60px); opacity: 0.3"
            ></div>

            <div class="container mx-auto relative z-10">
                @php
                    $filters = array_filter($filters, fn ($value) => $value !== null && $value !== '');
                @endphp

                @if (!empty($filters))
                    <div class="flex flex-wrap gap-2 mb-4 sm:mb-6">
                        @foreach ($filters as $key => $value)
                            @php
                                $newFilters = $filters;
                                unset($newFilters[$key]);
                                $query = http_build_query($newFilters);

                                $labels = [
                                    'category_id' => 'Kategorija',
                                    'tipas'       => 'Tipas',
                                    'min_price'   => 'Min. kaina',
                                    'max_price'   => 'Maks. kaina',
                                    'q'           => 'Paieška',
                                    'sort'        => 'Rūšiavimas',
                                    'city_id'     => 'Miestas',
                                ];

                                $label = $labels[$key] ?? ucfirst($key);

                                if ($key === 'category_id') {
                                    $value = \App\Models\Category::find($value)?->pavadinimas ?? $value;
                                }

                                if ($key === 'tipas') {
                                    $value = $value === 'preke' ? 'Prekė' : 'Paslauga';
                                }

                                if ($key === 'city_id') {
                                    $value = \App\Models\City::find($value)?->pavadinimas ?? $value;
                                }

                                if ($key === 'sort') {
                                    $value = match ($value) {
                                        'newest'     => 'Naujausi pirmiausia',
                                        'oldest'     => 'Seniausi pirmiausia',
                                        'price_asc'  => 'Kaina: nuo mažiausios',
                                        'price_desc' => 'Kaina: nuo didžiausios',
                                        default      => $value,
                                    };
                                }
                            @endphp

                            <a
                                href="{{ route('search.listings') }}?{{ $query }}"
                                class="px-3 py-1 rounded-full flex items-center gap-2 text-sm text-black"
                                style="background-color: rgb(215, 183, 142)"
                            >
                                <span>{{ $label }}: {{ $value }}</span>
                                <span class="font-bold">✕</span>
                            </a>
                        @endforeach

                        <a
                            href="{{ route('search.listings') }}"
                            class="px-3 py-1 rounded-full font-bold text-sm text-white"
                            style="background-color: rgb(184, 80, 54)"
                        >
                            Išvalyti viską
                        </a>
                    </div>
                @endif

                <div class="grid grid-cols-[repeat(auto-fit,minmax(260px,320px))] gap-4 sm:gap-6 justify-center">
                    @forelse ($listings as $item)
                        @php
                            $alreadyInCart = auth()->check()
                                ? (\App\Models\Cart::where('user_id', auth()->id())
                                    ->where('listing_id', $item->id)
                                    ->value('kiekis') ?? 0)
                                : 0;

                            $remainingToAdd = max(0, $item->kiekis - $alreadyInCart);
                        @endphp

                        <a
                            href="{{ route('listing.single', ['listing' => $item['id'], 'back' => request()->fullUrl()]) }}"
                            class="shadow rounded overflow-hidden hover:shadow-lg transition flex flex-col"
                            style="background-color: rgb(215, 183, 142)"
                        >
                            <div class="relative">
                                <div class="w-full h-56 sm:h-64 bg-white flex items-center justify-center overflow-hidden">
                                    @if($item->photos->isNotEmpty())
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($item->photos->first()->failo_url) }}"
                                            width="300"
                                            height="200"
                                            class="max-w-full max-h-full object-contain"
                                            alt="{{ $item->pavadinimas }}"
                                        >
                                    @else
                                        <img
                                            src="https://via.placeholder.com/300x200?text=No+Image"
                                            width="300"
                                            height="200"
                                            class="max-w-full max-h-full object-contain"
                                            alt="No image"
                                        >
                                    @endif
                                </div>
                                @auth
                                    @if(auth()->id() !== $item->user_id && auth()->user()->role !== 'admin')
                                        <button
                                            type="button"
                                            @click.stop.prevent="Alpine.store('favorites').toggle({{ $item->id }})"
                                            class="absolute top-2 right-2 z-30 w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center overflow-hidden"
                                            aria-label="Pažymėti kaip mėgstamą"
                                        >
                                            <span
                                                x-show="Alpine.store('favorites').has({{ $item->id }})"
                                                class="text-red-500 text-2xl leading-none"
                                            >
                                                🤎
                                            </span>

                                            <span
                                                x-show="!Alpine.store('favorites').has({{ $item->id }})"
                                                class="text-gray-200 text-2xl leading-none"
                                            >
                                                🤍
                                            </span>
                                        </button>
                                    @endif
                                @endauth
                            </div>

                            <div class="p-3 sm:p-4 flex flex-col flex-1 justify-end">
                                <div>
                                    <h2 class="text-base sm:text-lg font-semibold mb-1 leading-snug break-words whitespace-normal line-clamp-1">
                                        {{ $item['pavadinimas'] }}
                                    </h2>

                                    <p class="text-black text-sm break-words whitespace-normal line-clamp-1 hover:underline">
                                        {{ $item['aprasymas'] }}
                                    </p>
                                </div>

                                <div class="flex justify-between items-center pt-3 gap-2">
                                    <span class="font-bold text-base sm:text-lg" style="color: rgb(131, 99, 84)">
                                        {{ $item['kaina'] }} €
                                    </span>
                                    <div class="flex items-center gap-3">
                                        @if(auth()->check() && auth()->id() === $item->user_id)
                                            <span
                                                class="px-2 py-1 rounded text-xs text-black"
                                                style="background-color: rgb(207, 174, 134)"
                                                title="Tai jūsų skelbimas">
                                                Jūsų skelbimas
                                            </span>
                                    
                                        @elseif(auth()->check() && auth()->user()->role === 'admin')
                                            <span class="text-black font-semibold text-sm hover:underline">
                                                Plačiau →
                                            </span>
                                    
                                        @elseif($item->tipas === 'paslauga')
                                            <span
                                                class="px-2 py-1 rounded text-xs text-black"
                                                style="background-color: rgb(207, 174, 134)"
                                                title="Paslaugos nėra perkamos per krepšelį">
                                                Paslauga
                                            </span>
                                    
                                        @elseif(
                                            $item->statusas !== 'parduotas' &&
                                            !$item->is_hidden
                                        )
                                            @auth
                                                @if($remainingToAdd > 0)
                                                    <form method="POST" action="{{ route('cart.add', $item->id) }}" @click.stop>
                                                        @csrf
                                                        <button type="submit"
                                                            class="p-2 rounded text-black hover:text-white transition"
                                                            aria-label="Pridėti į krepšelį"
                                                            title="Pridėti į krepšelį">
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
                                                @else
                                                    <button type="button"
                                                            disabled
                                                            class="p-2 rounded text-[#eadcc8] cursor-not-allowed"
                                                            aria-label="Maksimalus kiekis jau krepšelyje"
                                                            title="Maksimalus kiekis jau krepšelyje">
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
                                                @endif
                                            @else
                                                <a href="{{ route('login') }}"
                                                   class="p-2 rounded text-black hover:text-white transition"
                                                   aria-label="Prisijunkite, kad pridėtumėte į krepšelį"
                                                   title="Prisijunkite, kad pridėtumėte į krepšelį"
                                                   @click.stop>
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor"
                                                         stroke-width="1.8"
                                                         class="w-6 h-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437m0 0L6.75 11.25m-1.644-5.977h13.239c.917 0 1.593.862 1.375 1.752l-1.273 5.25a1.125 1.125 0 01-1.094.86H6.75m-1.644-7.837L6.75 11.25m0 0L5.94 14.49a1.125 1.125 0 001.09 1.385h10.72M9 19.5a.75.75 0 100 1.5.75.75 0 000-1.5zm9 0a.75.75 0 100 1.5.75.75 0 000-1.5z" />
                                                    </svg>
                                                </a>
                                            @endauth
                                    
                                        @else
                                            <span
                                                class="px-2 py-1 rounded text-xs text-black"
                                                style="background-color: rgb(207, 174, 134)">
                                                Nepasiekiama
                                            </span>
                                        @endif
                                    </div>
                                    
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-600 text-center w-full">
                            Rezultatų nerasta.
                        </p>
                    @endforelse
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
</x-app-layout>
