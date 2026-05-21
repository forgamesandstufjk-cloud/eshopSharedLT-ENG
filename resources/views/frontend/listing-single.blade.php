<x-app-layout>
 <x-slot name="title">Konkretus skelbimas</x-slot>
<div class="min-h-screen flex flex-col" style="background-color: rgb(234, 220, 200)">
    <div
        class="max-w-6xl mx-auto w-full flex-1 py-6 sm:py-10 px-3 sm:px-4"
    >

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="mb-6 px-0 sm:px-4">
            <div class="px-4 py-3 rounded text-black" style="background-color: rgb(207, 174, 134); border: 1px solid #836354">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 px-0 sm:px-4">
            <div class="px-4 py-3 rounded text-black" style="background-color: rgb(207, 174, 134); border: 1px solid #836354">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- LISTING CARD --}}
    <div class="rounded-lg shadow p-4 sm:p-6" style="background-color: rgb(227, 197, 157)">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-10">

            {{-- LEFT: IMAGE GALLERY --}}
            <div>
                <div class="w-full h-[320px] sm:h-[450px] 2xl:h-[600px] bg-white rounded-lg mb-4 relative overflow-hidden">
                    <img
                        id="mainImage"
                        src="{{ $listing->photos->isNotEmpty()
                            ? \Illuminate\Support\Facades\Storage::disk('photos')->url($listing->photos->first()->failo_url)
                            : 'https://via.placeholder.com/600x450?text=No+Image'
                        }}"
                        alt="{{ $listing->pavadinimas }} nuotrauka"
                        class="w-full h-full object-contain"
                    />

                    @auth
                        @if(auth()->id() !== $listing->user_id && auth()->user()->role !== 'admin')
                            <button
                                type="button"
                                class="favorite-toggle absolute top-2 right-2 z-30 w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center overflow-hidden"
                                data-listing-id="{{ $listing->id }}"
                                aria-label="Pažymėti kaip mėgstamą"
                            >
                                <span class="favorite-on text-2xl leading-none hidden" style="color: rgb(104, 79, 67)">🤎</span>
                                <span class="favorite-off text-2xl leading-none text-white">🤍</span>
                            </button>
                        @endif
                    @endauth
                </div>

                @if($listing->photos->count() > 1)
                    <div class="flex gap-2 sm:gap-3 overflow-x-auto">
                        @foreach($listing->photos as $photo)
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded bg-white border flex items-center justify-center overflow-hidden shrink-0"
                                 style="border-color: #836354;">
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($photo->failo_url) }}"
                                    alt="{{ $listing->pavadinimas }} papildoma nuotrauka"
                                    class="w-full h-full object-contain cursor-pointer"
                                    onclick="document.getElementById('mainImage').src=this.src"
                                >
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- RIGHT: DETAILS --}}
            <div class="flex flex-col">

                {{-- CATEGORY --}}
                <div class="mb-3">
                    <span class="inline-block px-3 py-1 rounded text-sm text-white"
                          style="background-color: rgb(104, 79, 67)">
                        {{ $listing->Category->pavadinimas ?? 'Kategorija' }}
                    </span>
                </div>

                {{-- TITLE --}}
                <div class="flex items-start justify-between mb-4 gap-3">
                     <h1 class="text-2xl sm:text-3xl font-bold text-black leading-snug whitespace-normal break-words min-w-0 flex-1">
                        {{ $listing->pavadinimas }}
                    </h1>
                </div>

                {{-- DESCRIPTION --}}
                <div class="text-black leading-relaxed mb-6 whitespace-pre-line text-sm sm:text-base leading-snug break-words whitespace-normal">
                    {!! nl2br(e($listing->aprasymas)) !!}
                </div>

                {{-- PRICE --}}
                <div class="text-xl sm:text-2xl font-semibold text-black mb-2">
                   {{ number_format($listing->kaina, 2, ',', '.') }} €
                   <span class="text-black text-sm">
                       @if($listing->tipas === 'preke') / vnt @else / paslauga @endif
                   </span>
               </div>

                {{-- AVAILABLE --}}
                @if($listing->tipas === 'preke')
                    <div class="text-black mb-4">
                        <strong>Prieinama: </strong>
                        <span class="{{ $listing->kiekis == 0 ? 'font-bold' : '' }}"
                              style="{{ $listing->kiekis == 0 ? 'color: rgb(184, 80, 54);' : '' }}">
                            {{ $listing->kiekis }}
                        </span>
                    </div>
                @endif

                {{-- RENEWABLE BADGE --}}
                @if($listing->is_renewable)
                    <div class="mb-4">
                        <span class="inline-block px-3 py-1 rounded text-base text-white"
                              style="background-color: rgb(104, 79, 67)">
                            Atsinaujinanti prekė – pardavėjas papildo atsargas
                        </span>
                    </div>
                @endif

                {{-- CART OR EDIT --}}
                
                @if(auth()->check() && auth()->id() === $listing->user_id)
                    
                  @if($listing->tipas === 'paslauga')
                        <div class="mt-4 mb-3">
                            <a href="{{ route('seller.service-orders.create.from-listing', $listing->id) }}"
                               aria-label="Sukurti paslaugos užsakymą"
                               class="inline-block px-6 py-3 text-white rounded hover:text-black transition text-center w-full sm:w-auto"
                               style="background-color: rgb(104, 79, 67)">
                                Sukurti paslaugos užsakymą
                            </a>
                        </div>
                @endif  

                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-4">
                        <a href="{{ route('listing.edit', $listing->id) }}"
                          aria-label="Redaguoti"
                           class="px-6 py-3 text-white rounded hover:text-black transition text-center w-full sm:w-40 whitespace-nowrap"
                           style="background-color: rgb(104, 79, 67)">
                            Redaguoti
                        </a>

                        <form method="POST"
                              action="{{ route('listing.destroy', $listing->id) }}"
                              onsubmit="return confirm('Ar tikrai norite ištrinti šį skelbimą? Šio veiksmo atšaukti negalėsite.')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="px-6 py-3 text-white rounded hover:text-black transition text-center w-full sm:w-40"
                                style="background-color: rgb(184, 80, 54)">
                                Ištrinti skelbimą
                            </button>
                        </form>
                    </div>

                @elseif($listing->tipas === 'paslauga')

                    <div class="mt-4 text-black font-semibold">
                        Tai paslaugos skelbimas. Susisiekite su pardavėju dėl detalių.
                    </div>

                @else
                    {{-- ADD TO CART --}}
                    <form method="POST" action="{{ route('cart.add', $listing->id) }}"
                          class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        @csrf

                        {{-- quantity --}}
@php
    $alreadyInCart = auth()->check()
        ? \App\Models\Cart::where('user_id', auth()->id())
            ->where('listing_id', $listing->id)
            ->value('kiekis') ?? 0
        : 0;

    $remainingToAdd = max(0, $listing->kiekis - $alreadyInCart);
@endphp


<div class="flex items-center gap-1 quantity-selector" data-max="{{ max(1, $remainingToAdd) }}">
    <button
        type="button"
        class="qty-decrease w-10 h-10 border rounded flex items-center justify-center text-black transition-colors"
        style="background-color: rgb(234, 220, 200); border-color: #836354"
    >
        −
    </button>

    <input
        id="listing-quantity"
        type="number"
        name="quantity"
        value="1"
        min="1"
        max="{{ max(1, $remainingToAdd) }}"
        aria-label="Kiekis"
        class="qty-input w-12 h-10 text-center border rounded text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
        style="background-color: rgb(234, 220, 200); border-color: #836354"
    >

    <button
        type="button"
        class="qty-increase w-10 h-10 border rounded flex items-center justify-center text-black transition-colors"
        style="background-color: rgb(234, 220, 200); border-color: #836354"
    >
        +
    </button>
</div></div>

                        <button type="submit"
                            class="px-6 py-3 text-white rounded hover:text-black transition w-full sm:w-auto"
                            style="background-color: rgb(104, 79, 67)">
                           Pridėti į krepšelį              
                        </button>                 
                    </form>
                @endif
{{-- SELLER INFO --}}
<div class="mt-8 sm:mt-10 border-t pt-6" style="border-color: #836354">
    <h3 class="font-semibold text-black mb-2">Pardavėjas</h3>

    <div
        class="relative p-4 rounded border text-sm seller-contact-box"
        data-seller-url="{{ route('listing.seller-contact', $listing->id) }}"
        style="background-color: rgb(234, 220, 200); border-color: #836354"
    >

        @auth
            @if(auth()->id() !== $listing->user_id)
                <button
                    type="button"
                    title="Pranešti apie pardavėją"
                    class="seller-report-toggle absolute top-3 right-3 text-black hover:text-red-600 text-lg sm:text-xl leading-none"
                >
                    ⚐
                </button>
            @endif
        @endauth

        @if(auth()->check() && (auth()->id() === $listing->user_id || auth()->user()->role === 'admin'))
            <div class="text-black font-semibold text-base sm:text-lg pr-8">
                {{ $listing->user->vardas }} {{ $listing->user->pavarde }}
            </div>

            @if($listing->user->business_email)
                <div class="text-black mt-1">
                    El. paštas: {{ $listing->user->business_email }}
                </div>
            @endif

            @if($listing->user->telefonas)
                <div class="text-black mt-1">
                    Tel.: {{ $listing->user->telefonas }}
                </div>
            @endif

            @if($listing->user->address?->city)
                <div class="text-black mt-1">
                    Miestas: {{ $listing->user->address->city->pavadinimas }}
                </div>
            @endif
        @else
            <div class="pr-8 seller-hidden-block">
                <div class="text-black font-semibold text-base sm:text-lg">
                    Pardavėjo informacija paslėpta
                </div>

                <div class="text-black mt-1">
                    Kontaktai bus parodyti tik prisijungus.
                </div>

                @auth
                    <button
                        type="button"
                        class="seller-reveal-btn mt-3 px-4 py-2 rounded text-white hover:text-black transition-colors"
                        style="background-color: rgb(104, 79, 67)"
                    >
                        Rodyti pardavėjo kontaktus
                    </button>
                @else
                    <a
                        href="{{ route('login') }}"
                        aria-label="Prisijunkite, kad matytumėte kontaktus"
                        class="inline-block mt-3 px-4 py-2 rounded text-white hover:text-black transition-colors"
                        style="background-color: rgb(104, 79, 67)"
                    >
                        Prisijunkite, kad matytumėte kontaktus
                    </a>
                @endauth
            </div>

            <div class="seller-loading hidden mt-3 text-sm text-black">
                Kraunama...
            </div>

            <div class="seller-error hidden mt-3 text-sm" style="color: rgb(184, 80, 54)"></div>

            <div class="seller-revealed hidden">
                <div class="seller-name text-black font-semibold text-base sm:text-lg pr-8"></div>
                <div class="seller-email-row hidden text-black mt-1">
                    El. paštas: <span class="seller-email"></span>
                </div>
                <div class="seller-phone-row hidden text-black mt-1">
                    Tel.: <span class="seller-phone"></span>
                </div>
                <div class="seller-city-row hidden text-black mt-1">
                    Miestas: <span class="seller-city"></span>
                </div>
            </div>
        @endif

        @auth
            @if(auth()->id() !== $listing->user_id)
                <div class="seller-report-panel hidden mt-4 pt-4 border-t" style="border-color: #836354">
                    <form method="POST" action="{{ route('reports.store', $listing) }}" class="space-y-3">
                        @csrf

                        <div>
                            <label class="block text-black font-medium mb-1">Priežastis</label>
                            <select
                                name="reason"
                                required
                                class="w-full rounded border py-2 px-3 text-left text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                style="background-color: #d7b78e; border-color: #836354"
                            >
                                <option value="">Pasirinkite priežastį</option>
                                <option value="fraud">Sukčiavimas</option>
                                <option value="fake_item">Netikra prekė</option>
                                <option value="abuse">Įžeidžiantis elgesys</option>
                                <option value="spam">Šlamštas</option>
                                <option value="prohibited_items">Draudžiamos prekės</option>
                                <option value="other">Kita</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-black font-medium mb-1">Papildoma informacija</label>
                            <textarea
                                name="details"
                                rows="4"
                                class="border p-2 rounded w-full text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                style="background-color: rgb(227, 197, 157); border-color: #836354"
                                placeholder="Aprašykite situaciją, jei reikia"
                            ></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="submit"
                                class="px-4 py-2 rounded text-white hover:text-black transition-colors"
                                style="background-color: rgb(104, 79, 67)"
                            >
                                Siųsti pranešimą
                            </button>

                            <button
                                type="button"
                                class="seller-report-cancel px-4 py-2 rounded text-white hover:text-black transition-colors"
                                style="background-color: rgb(184, 80, 54)"
                            >
                                Atšaukti
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @endauth
    </div>
</div>
            </div>
        </div>
    </div>

{{-- OTHER PRODUCTS --}}
@if($similar->count() > 0)
<section class="mt-14 sm:mt-20">
    <h2 class="text-xl sm:text-2xl font-bold mb-6 text-black">Kiti šio pardavėjo produktai</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @foreach($similar as $s)
            @if($s->id !== $listing->id)
                @php
                    $alreadyInCart = auth()->check()
                        ? (\App\Models\Cart::where('user_id', auth()->id())
                            ->where('listing_id', $s->id)
                            ->value('kiekis') ?? 0)
                        : 0;

                    $remainingToAdd = max(0, $s->kiekis - $alreadyInCart);
                @endphp

                <div class="shadow rounded overflow-hidden flex flex-col h-full"
                     style="background-color: rgb(227, 197, 157)">

                    <a href="{{ route('listing.single', $s->id) }}"
                      aria-label=" Pereižrėkite skelbimą">
                        <div class="w-full h-56 sm:h-64 bg-white relative overflow-hidden">
                            <img
                                src="{{ $s->photos->isNotEmpty()
                                    ? \Illuminate\Support\Facades\Storage::disk('photos')->url($s->photos->first()->failo_url)
                                    : 'https://via.placeholder.com/300'
                                }}"
                                alt="{{ $s->pavadinimas }} nuotrauka"
                                class="w-full h-full object-contain"
                            >

                            @auth
                                @if(auth()->id() !== $s->user_id && auth()->user()->role !== 'admin')
                                    <button
                                        type="button"
                                        class="favorite-toggle absolute top-2 right-2 z-30 w-9 h-9 flex items-center justify-center overflow-hidden"
                                        data-listing-id="{{ $s->id }}"
                                        aria-label="Pažymėti kaip mėgstamą"
                                    >
                                        <span class="favorite-on text-2xl leading-none hidden" style="color: rgb(104, 79, 67)">🤎</span>
                                        <span class="favorite-off text-2xl leading-none text-white">🤍</span>
                                    </button>
                                @endif
                            @endauth
                        </div>
                    </a>

                    <div class="p-4 mt-auto min-h-[88px] flex flex-col justify-end">
                        <a href="{{ route('listing.single', $s->id) }}"
                           class="font-semibold text-black break-words whitespace-normal line-clamp-1">
                            {{ $s->pavadinimas }}
                        </a>
                        <a href="{{ route('listing.single', $s->id) }}"
                           class="text-black hover:underline line-clamp-1">
                            {{ $s->aprasymas }}
                        </a>

                        <div class="mt-2 flex items-center justify-between gap-2">
                            <div class="font-semibold" style="color: rgb(104, 79, 67)">
                                {{ number_format($s->kaina, 2, ',', '.') }} €
                            </div>

                            @if($s->tipas === 'paslauga')
                                <span
                                    class="px-2 py-1 rounded text-xs text-black"
                                    style="background-color: rgb(207, 174, 134)"
                                    title="Paslaugos nėra perkamos per krepšelį">
                                    Paslauga
                                </span>

                            @elseif(
                                $s->statusas !== 'parduotas' &&
                                !$s->is_hidden &&
                                (!auth()->check() || auth()->id() !== $s->user_id)
                            )
                                @auth
                                    @if($remainingToAdd > 0)
                                        <form method="POST" action="{{ route('cart.add', $s->id) }}">
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
                                                class="p-2 rounded text-gray-400 cursor-not-allowed"
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

                            @elseif(auth()->check() && auth()->id() === $s->user_id)
                                <span
                                    class="px-2 py-1 rounded text-xs text-black"
                                    style="background-color: rgb(207, 174, 134)"
                                    title="Tai jūsų skelbimas">
                                    Jūsų skelbimas
                                </span>

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
            @endif
        @endforeach
    </div>
</section>
@endif

{{-- REVIEWS SECTION --}}
<section id="reviews-section" class="mt-12 sm:mt-16">

@php
    $user = auth()->user();
    $isOwner = $user && $user->id === $listing->user_id;
    $isBanned = $user && $user->isBannedUser();

    $purchaseCount = $purchaseCount ?? 0;
    $reviewCount = $reviewCount ?? 0;
    $reviewsAllowed = $reviewsAllowed ?? false;
    $hasReviewed = $hasReviewed ?? false;
    $sort = $sort ?? request('sort', 'newest');

    $canLeaveReview = auth()->check()
        && !$isOwner
        && !$isBanned
        && $reviewsAllowed
        && ($purchaseCount > $reviewCount);
@endphp

<div class="grid grid-cols-1 {{ $canLeaveReview ? 'md:grid-cols-2' : '' }} gap-6 sm:gap-8 items-start">

    <div class="{{ $canLeaveReview ? '' : 'md:col-span-2' }}">
        <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-black">Atsiliepimai</h3>

        @if($totalReviews > 0)
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="text-2xl sm:text-3xl" style="color: rgb(104, 79, 67)">
                        {{ str_repeat('★', floor($avgRating)) }}
                    </div>
                    <div class="text-black">
                        <strong>{{ $avgRating }}</strong> / 5
                        <span class="text-black text-sm">
                            ({{ $totalReviews }} atsiliepimai)
                        </span>
                    </div>
                </div>

                <form
                    method="GET"
                    action="{{ route('listing.single', $listing->id) }}#reviews-section"
                    class="w-full sm:w-48"
                >
                    <label for="review-sort" class="sr-only">Rūšiuoti atsiliepimus</label>
                    <select
                        id="review-sort"
                        name="sort"
                        onchange="this.form.submit()"
                        class="w-full rounded border py-2 px-3 text-left text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                        style="background-color: rgb(227, 197, 157); border-color: #836354"
                    >
                        <option value="newest" @selected($sort === 'newest')>Naujausi</option>
                        <option value="oldest" @selected($sort === 'oldest')>Seniausi</option>
                        <option value="highest" @selected($sort === 'highest')>Geriausi</option>
                        <option value="lowest" @selected($sort === 'lowest')>Blogiausi</option>
                    </select>
                </form>
            </div>
        @endif

        <div class="space-y-4 review-list">
            @forelse($reviews as $review)
                <div
                    class="p-4 rounded border relative review-card"
                    data-review-id="{{ $review->id }}"
                    style="background-color: rgb(227, 197, 157); border-color: #836354"
                >
                    @auth
                        @if(auth()->id() === $review->user_id)
                            <button
                                type="button"
                                title="Redaguoti atsiliepimą"
                                class="review-edit-toggle absolute top-3 right-3 text-black hover:text-red-600 text-lg leading-none"
                            >
                                🖉
                            </button>
                        @elseif(auth()->id() !== $review->user_id)
                            <button
                                type="button"
                                title="Pranešti apie atsiliepimą"
                                class="review-report-toggle absolute top-3 right-3 text-black hover:text-red-600 text-lg leading-none"
                            >
                                ⚐
                            </button>
                        @endif
                    @endauth

                    <div class="review-display">
                        <div class="flex items-center gap-2 mb-1 pr-8">
                            <strong class="text-black">{{ $review->user->vardas }}</strong>
                            <span class="text-sm" style="color: rgb(104, 79, 67)">
                                {{ str_repeat('★', $review->ivertinimas) }}
                            </span>
                        </div>

                        <p class="text-black text-sm sm:text-base">
                            {{ $review->komentaras }}
                        </p>
                    </div>

                    @auth
                        @if(auth()->id() === $review->user_id)
                            <div class="review-edit-panel hidden">
                                <form method="POST" action="{{ route('review.update', $review->id) }}" class="space-y-3">
                                    @csrf
                                    @method('PUT')

                                    <div class="flex items-center gap-2 mb-1">
                                        <strong class="text-black">{{ $review->user->vardas }}</strong>
                                    </div>

                                    <div>
                                        <label class="block text-black font-medium mb-1" for="edit-rating-{{ $review->id }}">Įvertinimas</label>
                                        <select
                                            id="edit-rating-{{ $review->id }}"
                                            name="ivertinimas"
                                            class="w-full border border-gray-500 rounded p-3 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                            style="background-color: rgb(234, 220, 200)"
                                        >
                                            @for($n = 1; $n <= 5; $n++)
                                                <option value="{{ $n }}" @selected($review->ivertinimas == $n)>{{ $n }} / 5</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <textarea
                                        name="komentaras"
                                        rows="4"
                                        class="w-full border border-gray-500 rounded p-3 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                        style="background-color: rgb(234, 220, 200)"
                                        placeholder="Parašykite atsiliepimą..."
                                    >{{ $review->komentaras }}</textarea>

                                    <div class="flex gap-2">
                                        <button
                                            type="submit"
                                            class="text-white px-4 py-2 rounded hover:text-black"
                                            style="background-color: rgb(104, 79, 67)"
                                        >
                                            Išsaugoti
                                        </button>

                                        <button
                                            type="button"
                                            class="review-edit-cancel text-white px-4 py-2 rounded hover:text-black"
                                            style="background-color: rgb(184, 80, 54)"
                                        >
                                            Atšaukti
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endauth

                    @auth
                        @if(auth()->id() !== $review->user_id)
                            <div
                                class="review-report-panel hidden mt-4 pt-4 border-t"
                                style="border-color: #836354"
                            >
                                <form method="POST" action="{{ route('review.report', $review->id) }}" class="space-y-3">
                                    @csrf

                                    <div>
                                        <label class="block text-black font-medium mb-1" for="report-reason-{{ $review->id }}">Priežastis</label>
                                        <select
                                            id="report-reason-{{ $review->id }}"
                                            name="reason"
                                            required
                                            class="w-full rounded border py-2 px-3 text-left text-black flex justify-between items-center focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                            style="background-color: rgb(234, 220, 200); border-color: #836354"
                                        >
                                            <option value="">Pasirinkite priežastį</option>
                                            <option value="abuse">Įžeidžiantis tekstas</option>
                                            <option value="spam">Šlamštas</option>
                                            <option value="fake_review">Netikras atsiliepimas</option>
                                            <option value="harassment">Priekabiavimas</option>
                                            <option value="other">Kita</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-black font-medium mb-1">Papildoma informacija</label>
                                        <textarea
                                            name="details"
                                            rows="3"
                                            class="border p-2 rounded w-full text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                            style="background-color: rgb(234, 220, 200); border-color: #836354"
                                            placeholder="Aprašykite situaciją, jei reikia"
                                        ></textarea>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            type="submit"
                                            class="px-3 py-2 rounded text-white hover:text-black transition-colors"
                                            style="background-color: rgb(104, 79, 67)"
                                        >
                                            Siųsti
                                        </button>

                                        <button
                                            type="button"
                                            class="review-report-cancel px-3 py-2 rounded text-white hover:text-black transition-colors"
                                            style="background-color: rgb(184, 80, 54)"
                                        >
                                            Atšaukti
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            @empty
                <p class="text-black italic">Atsiliepimų dar nėra.</p>
            @endforelse
        </div>

        @if($reviews->hasPages())
            <div class="mt-6 text-black">
                {{ $reviews->appends(request()->except('reviews_page'))->fragment('reviews-section')->links() }}
            </div>
        @endif
    </div>

    @if(auth()->check() && !$isOwner && $purchaseCount > 0 && !$canLeaveReview)
        <div class="p-3 rounded text-black mb-4"
             style="background-color: rgb(207, 174, 134); border: 1px solid #836354">
            Jūs jau palikote atsiliepimą kiekvienam šio skelbimo pirkimui.
        </div>
    @endif

    @if($canLeaveReview)
        <div>
            <div>
                <h4 class="font-semibold mb-2 text-black">Palikti atsiliepimą</h4>

                <form method="POST" action="{{ route('review.store', $listing->id) }}"
                      class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-black font-medium mb-1" for="new-review-rating">Įvertinimas</label>
                        <select
                            id="new-review-rating"
                            name="ivertinimas"
                            required
                            class="w-full border border-gray-500 rounded p-3 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                            style="background-color: rgb(227, 197, 157)"
                        >
                            <option value="">Pasirinkite įvertinimą</option>
                            @for($n = 1; $n <= 5; $n++)
                                <option value="{{ $n }}">{{ $n }} / 5</option>
                            @endfor
                        </select>
                    </div>

                    <textarea
                        name="komentaras"
                        rows="4"
                        class="w-full border border-gray-500 rounded p-3 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                        style="background-color: rgb(227, 197, 157)"
                        placeholder="Parašykite atsiliepimą..."
                    ></textarea>

                    <button
                        type="submit"
                        class="text-white px-4 py-2 rounded w-full hover:text-black"
                        style="background-color: rgb(104, 79, 67)"
                    >
                        Pateikti atsiliepimą
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>
</section>

@auth
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = Array.from(document.querySelectorAll('.favorite-toggle'));
    let favorites = [];

    function normalizeIds(values) {
        return (values || []).map(v => String(v));
    }

    function hasFavorite(id) {
        return favorites.includes(String(id));
    }

    function renderFavorites() {
        buttons.forEach((button) => {
            const id = String(button.dataset.listingId);
            const on = button.querySelector('.favorite-on');
            const off = button.querySelector('.favorite-off');
            if (hasFavorite(id)) {
                on.classList.remove('hidden');
                off.classList.add('hidden');
            } else {
                on.classList.add('hidden');
                off.classList.remove('hidden');
            }
        });
    }

    async function loadFavorites() {
        if (!buttons.length) return;
        try {
            const res = await fetch('/api/favorites/ids', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            favorites = normalizeIds(data);
            renderFavorites();
        } catch (e) {
            console.error('Failed to load favorites', e);
        }
    }

    async function addFavorite(id) {
        return fetch('/api/favorite', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ listing_id: Number(id) }),
        });
    }

    async function removeFavorite(id) {
        return fetch(`/api/favorite/${encodeURIComponent(id)}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
    }

    buttons.forEach((button) => {
        button.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();
            const id = this.dataset.listingId;
            const isFav = hasFavorite(id);
            try {
                const res = isFav ? await removeFavorite(id) : await addFavorite(id);
                if (!res.ok) return;
                if (isFav) {
                    favorites = favorites.filter(favId => favId !== String(id));
                } else if (!hasFavorite(id)) {
                    favorites.push(String(id));
                }
                renderFavorites();
            } catch (e) {
                console.error('Failed to toggle favorite', e);
            }
        });
    });

    loadFavorites();

    document.querySelectorAll('.quantity-selector').forEach((wrap) => {
        const input = wrap.querySelector('.qty-input');
        const dec = wrap.querySelector('.qty-decrease');
        const inc = wrap.querySelector('.qty-increase');
        const max = Number(wrap.dataset.max || input.max || 1);

        function sync() {
            let val = parseInt(input.value || '1', 10);
            if (isNaN(val) || val < 1) val = 1;
            if (val > max) val = max;
            input.value = val;
            dec.disabled = val <= 1;
            inc.disabled = val >= max;
            dec.classList.toggle('opacity-50', val <= 1);
            dec.classList.toggle('cursor-not-allowed', val <= 1);
            inc.classList.toggle('opacity-50', val >= max);
            inc.classList.toggle('cursor-not-allowed', val >= max);
        }

        dec.addEventListener('click', () => { input.value = Math.max(1, (parseInt(input.value||'1',10) || 1) - 1); sync(); });
        inc.addEventListener('click', () => { input.value = Math.min(max, (parseInt(input.value||'1',10) || 1) + 1); sync(); });
        input.addEventListener('input', sync);
        sync();
    });

    document.querySelectorAll('.seller-contact-box').forEach((box) => {
        const revealBtn = box.querySelector('.seller-reveal-btn');
        const loading = box.querySelector('.seller-loading');
        const error = box.querySelector('.seller-error');
        const hiddenBlock = box.querySelector('.seller-hidden-block');
        const revealed = box.querySelector('.seller-revealed');
        const reportToggle = box.querySelector('.seller-report-toggle');
        const reportPanel = box.querySelector('.seller-report-panel');
        const reportCancel = box.querySelector('.seller-report-cancel');

        reportToggle?.addEventListener('click', () => reportPanel?.classList.toggle('hidden'));
        reportCancel?.addEventListener('click', () => reportPanel?.classList.add('hidden'));

        revealBtn?.addEventListener('click', async () => {
            if (revealBtn.disabled) return;
            revealBtn.disabled = true;
            loading?.classList.remove('hidden');
            error?.classList.add('hidden');
            error.textContent = '';

            try {
                const res = await fetch(box.dataset.sellerUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data?.message || 'Nepavyko gauti pardavėjo informacijos.');

                box.querySelector('.seller-name').textContent = data.name ?? '';
                if (data.email) {
                    box.querySelector('.seller-email').textContent = data.email;
                    box.querySelector('.seller-email-row').classList.remove('hidden');
                }
                if (data.phone) {
                    box.querySelector('.seller-phone').textContent = data.phone;
                    box.querySelector('.seller-phone-row').classList.remove('hidden');
                }
                if (data.city) {
                    box.querySelector('.seller-city').textContent = data.city;
                    box.querySelector('.seller-city-row').classList.remove('hidden');
                }

                hiddenBlock?.classList.add('hidden');
                revealed?.classList.remove('hidden');
            } catch (err) {
                error.textContent = err.message || 'Nepavyko gauti pardavėjo informacijos.';
                error.classList.remove('hidden');
                revealBtn.disabled = false;
            } finally {
                loading?.classList.add('hidden');
            }
        });
    });

    document.querySelectorAll('.review-card').forEach((card) => {
        const display = card.querySelector('.review-display');
        const editPanel = card.querySelector('.review-edit-panel');
        const reportPanel = card.querySelector('.review-report-panel');
        card.querySelector('.review-edit-toggle')?.addEventListener('click', () => {
            display?.classList.add('hidden');
            editPanel?.classList.remove('hidden');
        });
        card.querySelector('.review-edit-cancel')?.addEventListener('click', () => {
            editPanel?.classList.add('hidden');
            display?.classList.remove('hidden');
        });
        card.querySelector('.review-report-toggle')?.addEventListener('click', () => {
            reportPanel?.classList.toggle('hidden');
        });
        card.querySelector('.review-report-cancel')?.addEventListener('click', () => {
            reportPanel?.classList.add('hidden');
        });
    });
});
</script>
@endauth
@include('components.footer')
</div>
</x-app-layout>
