<x-app-layout>

<style>
/* Remove number input arrows (Chrome, Safari, Edge) */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Remove number input arrows (Firefox) */
input[type=number] {
    -moz-appearance: textfield;
}
</style>

<div class="min-h-screen flex flex-col" style="background-color: rgb(234, 220, 200)">
    <div
        x-data
        x-init="if ({{ auth()->check() ? 'true' : 'false' }}) Alpine.store('favorites').load()"
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
    <div class="rounded-lg shadow p-4 sm:p-6" style="background-color: rgb(215, 183, 142)">

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
                        class="w-full h-full object-contain"
                    />

                    @auth
                        @if(auth()->id() !== $listing->user_id && auth()->user()->role !== 'admin')
                            <button
                                type="button"
                                @click.stop.prevent="Alpine.store('favorites').toggle({{ $listing->id }})"
                                class="absolute top-2 right-2 z-30 w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center overflow-hidden"
                                aria-label="Pažymėti kaip mėgstamą"
                            >
                                <span
                                    x-show="Alpine.store('favorites').has({{ $listing->id }})"
                                    class="text-2xl leading-none"
                                    style="color: rgb(131, 99, 84)"
                                >
                                    🤎
                                </span>

                                <span
                                    x-show="!Alpine.store('favorites').has({{ $listing->id }})"
                                    class="text-2xl leading-none text-white"
                                >
                                    🤍
                                </span>
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
                          style="background-color: rgb(131, 99, 84)">
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
                        <span class="inline-block px-3 py-1 rounded text-sm text-black"
                              style="background-color: rgb(131, 99, 84)">
                            Atsinaujinanti prekė – pardavėjas papildo atsargas
                        </span>
                    </div>
                @endif

                {{-- CART OR EDIT --}}
                
                @if(auth()->check() && auth()->id() === $listing->user_id)
                    
                  @if($listing->tipas === 'paslauga')
                        <div class="mt-4 mb-3">
                            <a href="{{ route('seller.service-orders.create.from-listing', $listing->id) }}"
                               class="inline-block px-6 py-3 text-white rounded hover:text-black transition text-center w-full sm:w-auto"
                               style="background-color: rgb(131, 99, 84)">
                                Sukurti paslaugos užsakymą
                            </a>
                        </div>
                @endif  

                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-4">
                        <a href="{{ route('listing.edit', $listing->id) }}"
                           class="px-6 py-3 text-white rounded hover:text-black transition text-center w-full sm:w-40 whitespace-nowrap"
                           style="background-color: rgb(131, 99, 84)">
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


<div class="flex items-center gap-1" x-data="{ qty: 1, max: {{ max(1, $remainingToAdd) }} }">
    <button
        type="button"
        @click="qty = Math.max(1, qty - 1)"
        :disabled="qty <= 1"
        :class="qty <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:text-white cursor-pointer'"
        class="w-10 h-10 border rounded flex items-center justify-center text-black transition-colors"
        style="background-color: rgb(234, 220, 200); border-color: #836354"
        @mouseenter="if(qty > 1) $el.style.backgroundColor = 'rgb(131, 99, 84)'"
        @mouseleave="$el.style.backgroundColor = 'rgb(234, 220, 200)'"
    >
        −
    </button>

    <input
        type="number"
        name="quantity"
        x-model="qty"
        value="1"
        min="1"
        max="{{ max(1, $remainingToAdd) }}"
        class="w-12 h-10 text-center border rounded text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
        style="background-color: rgb(234, 220, 200); border-color: #836354"
    >

    <button
        type="button"
        @click="if (qty < max) qty++"
        :disabled="qty >= max"
        :class="qty >= max ? 'opacity-50 cursor-not-allowed' : 'hover:text-white cursor-pointer'"
        class="w-10 h-10 border rounded flex items-center justify-center text-black transition-colors"
        style="background-color: rgb(234, 220, 200); border-color: #836354"
        @mouseenter="if(qty < max) $el.style.backgroundColor = 'rgb(131, 99, 84)'"
        @mouseleave="$el.style.backgroundColor = 'rgb(234, 220, 200)'"
    >
        +
    </button>
</div>

                        <button type="submit"
                            class="px-6 py-3 text-white rounded hover:text-black transition w-full sm:w-auto"
                            style="background-color: rgb(131, 99, 84)">
                           Pridėti į krepšelį              
                        </button>                 
                    </form>
                @endif

{{-- SELLER INFO --}}
<div class="mt-8 sm:mt-10 border-t pt-6" style="border-color: #836354">
    <h3 class="font-semibold text-black mb-2">Pardavėjas</h3>

    <div x-data="{ openReport: false, reasonOpen: false, selectedReason: '' }"
         class="relative p-4 rounded border text-sm"
         style="background-color: rgb(234, 220, 200); border-color: #836354">

        @auth
            @if(auth()->id() !== $listing->user_id)
                <button
                    type="button"
                    @click="openReport = !openReport"
                    title="Pranešti apie pardavėją"
                    class="absolute top-3 right-3 text-black hover:text-red-600 text-lg sm:text-xl leading-none"
                >
                    ⚐
                </button>
            @endif
        @endauth

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

        @auth
            @if(auth()->id() !== $listing->user_id)
                <div x-show="openReport" x-cloak class="mt-4 pt-4 border-t" style="border-color: #836354">
                    <form method="POST" action="{{ route('reports.store', $listing) }}" class="space-y-3">
                        @csrf

                        <div>
                            <label class="block text-black font-medium mb-1">Priežastis</label>

                            <div class="relative">
                                <input type="hidden" name="reason" :value="selectedReason" required>

                                <button
                                    type="button"
                                    @click="reasonOpen = !reasonOpen"
                                    :class="reasonOpen ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                                    class="w-full rounded border py-2 px-3 text-left text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                                    style="background-color: #d7b78e; border-color: #836354"
                                >
                                    <span x-text="
                                        selectedReason === '' ? 'Pasirinkite priežastį' :
                                        selectedReason === 'fraud' ? 'Sukčiavimas' :
                                        selectedReason === 'fake_item' ? 'Netikra prekė' :
                                        selectedReason === 'abuse' ? 'Įžeidžiantis tekstas' :
                                        selectedReason === 'spam' ? 'Nepadorus turinys' :
                                        selectedReason === 'prohibited_items' ? 'Draudžiamos prekės' :
                                        'Kita'
                                    "></span>

                                    <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div
                                    x-show="reasonOpen"
                                    @click.outside="reasonOpen = false"
                                    class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50"
                                    style="background-color: rgb(215, 183, 142); border-color: #836354"
                                >

                                    <div
                                        @click="selectedReason = 'fraud'; reasonOpen = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                    >
                                        Sukčiavimas
                                    </div>

                                    <div
                                        @click="selectedReason = 'fake_item'; reasonOpen = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                    >
                                        Netikra prekė
                                    </div>

                                    <div
                                        @click="selectedReason = 'abuse'; reasonOpen = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                    >
                                        Įžeidžiantis elgesys
                                    </div>

                                    <div
                                        @click="selectedReason = 'spam'; reasonOpen = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                    >
                                        Šlamštas
                                    </div>

                                    <div
                                        @click="selectedReason = 'prohibited_items'; reasonOpen = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                    >
                                        Draudžiamos prekės
                                    </div>

                                    <div
                                        @click="selectedReason = 'other'; reasonOpen = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                    >
                                        Kita
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-black font-medium mb-1">Papildoma informacija</label>
                            <textarea
                                name="details"
                                rows="4"
                                class="border p-2 rounded w-full text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                style="background-color: rgb(215, 183, 142); border-color: #836354"
                                placeholder="Aprašykite situaciją, jei reikia"
                            ></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="submit"
                                class="px-4 py-2 rounded text-white hover:text-black transition-colors"
                                style="background-color: rgb(131, 99, 84)"
                            >
                                Siųsti pranešimą
                            </button>

                            <button
                                type="button"
                                @click="openReport = false"
                                class="px-4 py-2 rounded text-white hover:text-black transition-colors"
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
    <h2 class="text-xl sm:text-2xl font-bold mb-6">Kiti šio pardavėjo produktai</h2>

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
                     style="background-color: rgb(215, 183, 142)">

                    <a href="{{ route('listing.single', $s->id) }}">
                        <div class="w-full h-56 sm:h-64 bg-white relative overflow-hidden">
                            <img
                                src="{{ $s->photos->isNotEmpty()
                                    ? \Illuminate\Support\Facades\Storage::disk('photos')->url($s->photos->first()->failo_url)
                                    : 'https://via.placeholder.com/300'
                                }}"
                                class="w-full h-full object-contain"
                            >

                            @auth
                                @if(auth()->id() !== $s->user_id && auth()->user()->role !== 'admin')
                                    <button
                                        type="button"
                                        @click.stop.prevent="Alpine.store('favorites').toggle({{ $s->id }})"
                                        class="absolute top-2 right-2 z-30 w-9 h-9 flex items-center justify-center overflow-hidden"
                                        aria-label="Pažymėti kaip mėgstamą"
                                    >
                                        <span
                                            x-show="Alpine.store('favorites').has({{ $s->id }})"
                                            class="text-2xl leading-none"
                                            style="color: rgb(131, 99, 84)"
                                        >
                                            🤎
                                        </span>

                                        <span
                                            x-show="!Alpine.store('favorites').has({{ $s->id }})"
                                            class="text-2xl leading-none text-white"
                                        >
                                            🤍
                                        </span>
                                    </button>
                                @endif
                            @endauth
                        </div>
                    </a>

                    <div class="p-4 mt-auto min-h-[88px] flex flex-col justify-end">
                        <a href="{{ route('listing.single', $s->id) }}"
                           class="font-semibold break-words whitespace-normal line-clamp-1">
                            {{ $s->pavadinimas }}
                        </a>
                        <a href="{{ route('listing.single', $s->id) }}"
                           class="hover:underline line-clamp-1">
                            {{ $s->aprasymas }}
                        </a>

                        <div class="mt-2 flex items-center justify-between gap-2">
                            <div class="font-semibold" style="color: rgb(131, 99, 84)">
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
<section class="mt-12 sm:mt-16">

@php
    $user = auth()->user();
    $isOwner = $user && $user->id === $listing->user_id;
    $isBanned = $user && $user->isBannedUser();

    $purchaseCount = $purchaseCount ?? 0;
    $reviewCount = $reviewCount ?? 0;
    $reviewsAllowed = $reviewsAllowed ?? false;
    $hasReviewed = $hasReviewed ?? false;

    $canLeaveReview = auth()->check()
        && !$isOwner
        && !$isBanned
        && $reviewsAllowed
        && ($purchaseCount > $reviewCount);

    $sort = request('sort', 'newest');

    $sortedReviews = match($sort) {
        'oldest'  => $listing->review->sortBy('created_at'),
        'highest' => $listing->review->sortByDesc('ivertinimas'),
        'lowest'  => $listing->review->sortBy('ivertinimas'),
        default   => $listing->review->sortByDesc('created_at'),
    };

    $avgRating = round($listing->review->avg('ivertinimas'), 1);
    $totalReviews = $listing->review->count();
@endphp

<div
    x-data="{ editingReviewId: null }"
    class="grid grid-cols-1 {{ $canLeaveReview ? 'md:grid-cols-2' : '' }} gap-6 sm:gap-8 items-start"
>

    <div class="{{ $canLeaveReview ? '' : 'md:col-span-2' }}">
        <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-black">Atsiliepimai</h3>

        @if($totalReviews > 0)
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="text-2xl sm:text-3xl" style="color: rgb(131, 99, 84)">
                        {{ str_repeat('★', floor($avgRating)) }}
                    </div>
                    <div class="text-black">
                        <strong>{{ $avgRating }}</strong> / 5
                        <span class="text-black text-sm">
                            ({{ $totalReviews }} atsiliepimai)
                        </span>
                    </div>
                </div>

                <form method="GET" class="w-full sm:w-48" x-data="{ sortOpen: false, selectedSort: '{{ request('sort', 'newest') }}' }">
                    <div class="relative">
                        <input type="hidden" name="sort" :value="selectedSort">

                        <button
                            type="button"
                            @click="sortOpen = !sortOpen"
                            :class="sortOpen ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                            class="w-full rounded border py-2 px-3 text-left text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                            style="background-color: rgb(215, 183, 142)"
                        >
                            <span x-text="
                                selectedSort === 'newest' ? 'Naujausi' :
                                selectedSort === 'oldest' ? 'Seniausi' :
                                selectedSort === 'highest' ? 'Geriausi' :
                                'Blogiausi'
                            "></span>

                            <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div
                            x-show="sortOpen"
                            @click.outside="sortOpen = false"
                            class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50"
                            style="background-color: rgb(215, 183, 142); border-color: #836354"
                        >
                            <div
                                @click="selectedSort = 'newest'; sortOpen = false; $nextTick(() => $el.closest('form').submit())"
                                class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354]"
                            >
                                Naujausi
                            </div>

                            <div
                                @click="selectedSort = 'oldest'; sortOpen = false; $nextTick(() => $el.closest('form').submit())"
                                class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354]"
                            >
                                Seniausi
                            </div>

                            <div
                                @click="selectedSort = 'highest'; sortOpen = false; $nextTick(() => $el.closest('form').submit())"
                                class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354]"
                            >
                                Geriausi
                            </div>

                            <div
                                @click="selectedSort = 'lowest'; sortOpen = false; $nextTick(() => $el.closest('form').submit())"
                                class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354]"
                            >
                                Blogiausi
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        {{-- LEFT: REVIEWS --}}
        <div class="space-y-4">
            @forelse($sortedReviews as $review)
                <div
                    x-data="{
                        openReportReview: false,
                        reasonOpen: false,
                        selectedReason: '',
                        editText: @js($review->komentaras ?? ''),
                        originalText: @js($review->komentaras ?? ''),
                        editStars: {{ (int) $review->ivertinimas }},
                        originalStars: {{ (int) $review->ivertinimas }},
                        hoverStars: 0
                    }"
                    class="p-4 rounded border relative"
                    style="background-color: rgb(215, 183, 142); border-color: #836354"
                >
                    @auth
                        @if(auth()->id() === $review->user_id)
                            <button
                                x-show="editingReviewId === null"
                                x-cloak
                                type="button"
                                @click="
                                    editingReviewId = {{ $review->id }};
                                    editText = originalText;
                                    editStars = originalStars;
                                    hoverStars = 0;
                                "
                                title="Redaguoti atsiliepimą"
                                class="absolute top-3 right-3 text-black hover:text-red-600 text-lg leading-none"
                            >
                                🖉
                            </button>
                        @elseif(auth()->id() !== $review->user_id)
                            <button
                                type="button"
                                @click="openReportReview = !openReportReview"
                                title="Pranešti apie atsiliepimą"
                                class="absolute top-3 right-3 text-black hover:text-red-600 text-lg leading-none"
                            >
                                ⚐
                            </button>
                        @endif
                    @endauth

                    {{-- VIEW MODE --}}
                    <div x-show="editingReviewId !== {{ $review->id }}">
                        <div class="flex items-center gap-2 mb-1 pr-8">
                            <strong class="text-black">{{ $review->user->vardas }}</strong>
                            <span class="text-sm" style="color: rgb(131, 99, 84)">
                                {{ str_repeat('★', $review->ivertinimas) }}
                            </span>
                        </div>

                        <p class="text-black text-sm sm:text-base">
                            {{ $review->komentaras }}
                        </p>
                    </div>

                    {{-- EDIT MODE --}}
                    @auth
                        @if(auth()->id() === $review->user_id)
                            <div x-show="editingReviewId === {{ $review->id }}" x-cloak>
                                <form method="POST" action="{{ route('review.update', $review->id) }}" class="space-y-3">
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="ivertinimas" :value="editStars">

                                    <div class="flex items-center gap-2 mb-1">
                                        <strong class="text-black">{{ $review->user->vardas }}</strong>
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-1 mb-2">
                                            @for($n = 1; $n <= 5; $n++)
                                                <button
                                                    type="button"
                                                    @mouseenter="hoverStars = {{ $n }}"
                                                    @mouseleave="hoverStars = 0"
                                                    @click="editStars = {{ $n }}"
                                                    class="text-3xl leading-none focus:outline-none"
                                                    :aria-label="'{{ $n }} žvaigždutės'"
                                                >
                                                    <span
                                                        x-show="(hoverStars || editStars) >= {{ $n }}"
                                                        style="color: rgb(131, 99, 84)">
                                                        ★
                                                    </span>
                                                    <span
                                                        x-show="(hoverStars || editStars) < {{ $n }}"
                                                        class="text-gray-400"
                                                    >
                                                        ☆
                                                    </span>
                                                </button>
                                            @endfor
                                        </div>

                                        <div class="text-sm text-black">
                                            <span x-text="editStars + ' / 5'"></span>
                                        </div>
                                    </div>

                                    <textarea
                                        name="komentaras"
                                        rows="4"
                                        x-model="editText"
                                        class="w-full border border-gray-500 rounded p-3 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                        style="background-color: rgb(234, 220, 200)"
                                        placeholder="Parašykite atsiliepimą..."
                                    ></textarea>

                                    <div class="flex gap-2">
                                        <button
                                            type="submit"
                                            class="text-white px-4 py-2 rounded hover:text-black"
                                            style="background-color: rgb(131, 99, 84)"
                                            :disabled="editStars === 0"
                                            :class="editStars === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                                        >
                                            Išsaugoti
                                        </button>

                                        <button
                                            type="button"
                                            @click="
                                                editingReviewId = null;
                                                editText = originalText;
                                                editStars = originalStars;
                                                hoverStars = 0;
                                            "
                                            class="text-white px-4 py-2 rounded hover:text-black"
                                            style="background-color: rgb(184, 80, 54)"
                                        >
                                            Atšaukti
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endauth

                    {{-- REPORT FORM --}}
                    @auth
                        @if(auth()->id() !== $review->user_id)
                            <div
                                x-show="openReportReview"
                                x-cloak
                                class="mt-4 pt-4 border-t"
                                style="border-color: #836354"
                            >
                                <form method="POST" action="{{ route('review.report', $review->id) }}" class="space-y-3">
                                    @csrf

                                    <div>
                                        <label class="block text-black font-medium mb-1">Priežastis</label>

                                        <div class="relative">
                                            <input type="hidden" name="reason" :value="selectedReason" required>

                                            <button
                                                type="button"
                                                @click="reasonOpen = !reasonOpen"
                                                class="w-full rounded border py-2 px-3 text-left text-black flex justify-between items-center focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                                style="background-color: rgb(234, 220, 200); border-color: #836354"
                                            >
                                                <span x-text="
                                                    selectedReason === '' ? 'Pasirinkite priežastį' :
                                                    selectedReason === 'abuse' ? 'Įžeidžiantis tekstas' :
                                                    selectedReason === 'spam' ? 'Šlamštas' :
                                                    selectedReason === 'fake_review' ? 'Netikras atsiliepimas' :
                                                    selectedReason === 'harassment' ? 'Priekabiavimas' :
                                                    'Kita'
                                                "></span>

                                                <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <div
                                                x-show="reasonOpen"
                                                @click.outside="reasonOpen = false"
                                                class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50"
                                                style="background-color: rgb(215, 183, 142); border-color: #836354"
                                            >
                                                <div
                                                    @click="selectedReason = 'abuse'; reasonOpen = false"
                                                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                                >
                                                    Įžeidžiantis tekstas
                                                </div>

                                                <div
                                                    @click="selectedReason = 'spam'; reasonOpen = false"
                                                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                                >
                                                    Šlamštas
                                                </div>

                                                <div
                                                    @click="selectedReason = 'fake_review'; reasonOpen = false"
                                                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                                >
                                                    Netikras atsiliepimas
                                                </div>

                                                <div
                                                    @click="selectedReason = 'harassment'; reasonOpen = false"
                                                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                                >
                                                    Priekabiavimas
                                                </div>

                                                <div
                                                    @click="selectedReason = 'other'; reasonOpen = false"
                                                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354] hover:text-white"
                                                >
                                                    Kita
                                                </div>
                                            </div>
                                        </div>
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
                                            style="background-color: rgb(131, 99, 84)"
                                        >
                                            Siųsti
                                        </button>

                                        <button
                                            type="button"
                                            @click="openReportReview = false; reasonOpen = false; selectedReason = ''"
                                            class="px-3 py-2 rounded text-white hover:text-black transition-colors"
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
    </div>

    {{-- RIGHT: REVIEW FORM --}}
    @if(auth()->check() && !$isOwner && $purchaseCount > 0 && !$canLeaveReview)
        <div class="p-3 rounded text-black mb-4"
             style="background-color: rgb(207, 174, 134); border: 1px solid #836354">
            Jūs jau palikote atsiliepimą kiekvienam šio skelbimo pirkimui.
        </div>
    @endif
        
    @if($canLeaveReview)
        <div x-show="editingReviewId === null">
            <div x-data="{ selectedStars: 0, hoverStars: 0 }">
                <h4 class="font-semibold mb-2 text-black">Palikti atsiliepimą</h4>

                <form method="POST" action="{{ route('review.store', $listing->id) }}"
                      class="space-y-3">
                    @csrf

                    <input type="hidden" name="ivertinimas" :value="selectedStars">

                    <div>
                        <div class="flex items-center gap-1 mb-2">
                            @for($n = 1; $n <= 5; $n++)
                                <button
                                    type="button"
                                    @mouseenter="hoverStars = {{ $n }}"
                                    @mouseleave="hoverStars = 0"
                                    @click="selectedStars = {{ $n }}"
                                    class="text-3xl leading-none focus:outline-none"
                                    :aria-label="'{{ $n }} žvaigždutės'"
                                >
                                    <span
                                        x-show="(hoverStars || selectedStars) >= {{ $n }}"
                                        style="color: rgb(131, 99, 84)">
                                        ★
                                    </span>
                                    <span
                                        x-show="(hoverStars || selectedStars) < {{ $n }}"
                                        class="text-gray-400"
                                    >
                                        ☆
                                    </span>
                                </button>
                            @endfor
                        </div>

                        <div class="text-sm text-black">
                            <span x-show="selectedStars === 0">Pasirinkite įvertinimą</span>
                            <span x-show="selectedStars > 0" x-text="selectedStars + ' / 5'"></span>
                        </div>
                    </div>

                    <textarea
                        name="komentaras"
                        rows="4"
                        class="w-full border border-gray-500 rounded p-3 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                        style="background-color: rgb(215, 183, 142)"
                        placeholder="Parašykite atsiliepimą..."
                    ></textarea>

                    <button
                        type="submit"
                        class="text-white px-4 py-2 rounded w-full hover:text-black"
                        style="background-color: rgb(131, 99, 84)"
                        :disabled="selectedStars === 0"
                        :class="selectedStars === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                    >
                        Pateikti atsiliepimą
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>
</section>
                    </div>
  @include('components.footer')
</div>
</x-app-layout>
