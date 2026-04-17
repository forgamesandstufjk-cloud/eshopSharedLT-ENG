<x-app-layout>
    <div class="min-h-screen flex flex-col" style="background-color: rgb(234, 220, 200)">
        <div
            x-data
            x-init="if ({{ auth()->check() ? 'true' : 'false' }}) Alpine.store('favorites').load()"
            class="relative flex-1 w-full px-3 sm:px-4 mt-6 sm:mt-8 pb-10"
        >
            <div
                class="fixed inset-0 pointer-events-none bg-no-repeat bg-center bg-contain"
                style="background-image: url('{{ asset('images/vytis.png') }}'); background-size: 500px 500px; background-position: center calc(50% + 60px); opacity: 0.3"
            ></div>

            <div class="container mx-auto relative z-10">
                <div class="grid grid-cols-[repeat(auto-fit,minmax(260px,320px))] gap-4 sm:gap-6 justify-center">
                    @forelse ($listings as $item)
                        <div
                            class="shadow rounded overflow-hidden hover:shadow-lg transition flex flex-col"
                            style="background-color: rgb(215, 183, 142)"
                        >
                            <a href="{{ route('listing.single', ['listing' => $item->id, 'back' => request()->fullUrl()]) }}"
                               class="block relative">
                                <div class="w-full h-56 sm:h-64 bg-white flex items-center justify-center overflow-hidden">
                                    @if($item->photos->isNotEmpty())
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($item->photos->first()->failo_url) }}"
                                            class="max-w-full max-h-full object-contain"
                                        >
                                    @else
                                        <img
                                            src="https://via.placeholder.com/300"
                                            class="max-w-full max-h-full object-contain"
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
                            </a>

                            <div class="p-3 sm:p-4 flex flex-col flex-1 justify-end">
                                <div>
                                    <a href="{{ route('listing.single', ['listing' => $item->id, 'back' => request()->fullUrl()]) }}"
                                       class="block">
                                        <h2 class="text-base sm:text-lg font-semibold mb-1 leading-snug break-words whitespace-normal line-clamp-1">
                                            {{ $item->pavadinimas }}
                                        </h2>
                                    </a>

                                    <a href="{{ route('listing.single', ['listing' => $item->id, 'back' => request()->fullUrl()]) }}"
                                       class="block text-black hover:underline">
                                        <p class="text-black text-sm break-words whitespace-normal line-clamp-1">
                                            {{ $item->aprasymas }}
                                        </p>
                                    </a>
                                </div>

                                @php
                                    $alreadyInCart = auth()->check()
                                        ? (\App\Models\Cart::where('user_id', auth()->id())
                                            ->where('listing_id', $item->id)
                                            ->value('kiekis') ?? 0)
                                        : 0;

                                    $remainingToAdd = max(0, $item->kiekis - $alreadyInCart);
                                @endphp

                                <div class="flex justify-between items-center pt-3 gap-2">
                                    <span class="font-bold text-base sm:text-lg" style="color: rgb(131, 99, 84)">
                                        {{ $item->kaina }} €
                                    </span>

                                    <div class="flex items-center gap-3">
                                        @if(auth()->check() && auth()->id() === $item->user_id)
                                            <span
                                                class="px-2 py-1 rounded text-xs text-black"
                                                style="background-color: rgb(207, 174, 134)"
                                                title="Tai jūsų skelbimas">
                                                Jūsų skelbimas
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
                                                    <form method="POST" action="{{ route('cart.add', $item->id) }}">
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
                                                   title="Prisijunkite, kad pridėtumėte į krepšelį">
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
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-black text-center col-span-full">
                            Skelbimų nerasta
                        </p>
                    @endforelse
                </div>

                @if(method_exists($listings, 'links'))
                    <div class="mt-8 flex justify-center relative z-10">
                        {{ $listings->links() }}
                    </div>
                @endif
            </div>
        </div>

        @include('components.footer')
    </div>
</x-app-layout>
