<x-app-layout>
    <div class="max-w-6xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0">
        <h1 class="text-xl sm:text-2xl font-bold mb-2 text-black">
            Atsiliepimų palyginimas
        </h1>

        <div class="text-black mb-4">
            Skelbimas: <strong>{{ $listing->pavadinimas ?? '—' }}</strong>
        </div>

        <div class="mb-6">
            <a
                href="{{ route('admin.reported-listings.user-comments', $user->id) }}"
                class="inline-block px-4 py-2 rounded text-white hover:text-black"
                style="background-color: rgb(131, 99, 84)">
                ← Atgal į komentarus
            </a>
        </div>

        <div class="rounded-lg shadow p-4 sm:p-6" style="background-color: rgb(215, 183, 142)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-10">
                <div class="relative">
                    <img
                        id="mainImage"
                        src="{{ $listing->photos->isNotEmpty()
                            ? \Illuminate\Support\Facades\Storage::disk('photos')->url($listing->photos->first()->failo_url)
                            : 'https://via.placeholder.com/600x450?text=No+Image'
                        }}"
                        class="rounded-lg shadow w-full max-h-[320px] sm:max-h-[450px] object-cover mb-4">

                    @if ($listing->photos->count() > 1)
                        <div class="flex gap-2 sm:gap-3 overflow-x-auto">
                            @foreach ($listing->photos as $photo)
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($photo->failo_url) }}"
                                    class="w-16 h-16 sm:w-20 sm:h-20 rounded object-cover cursor-pointer border"
                                    style="border-color: #836354"
                                    onclick="document.getElementById('mainImage').src=this.src">
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex flex-col">
                    <div class="mb-3">
                        <span
                            class="inline-block px-3 py-1 rounded text-sm text-white"
                            style="background-color: rgb(131, 99, 84)">
                            {{ $listing->Category->pavadinimas ?? 'Kategorija' }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between mb-4 gap-3">
                        <h1 class="text-2xl sm:text-3xl font-bold text-black leading-snug">
                            {{ $listing->pavadinimas }}
                        </h1>
                    </div>

                    <div class="text-black leading-relaxed mb-6 whitespace-pre-line text-sm sm:text-base">
                        {!! nl2br(e($listing->aprasymas)) !!}
                    </div>

                    <div class="text-xl sm:text-2xl font-semibold text-black mb-2">
                        {{ number_format($listing->kaina, 2, ',', '.') }} €
                        <span class="text-black text-sm">
                            @if ($listing->tipas === 'preke')
                                / vnt
                            @else
                                / paslauga
                            @endif
                        </span>
                    </div>

                    @if ($listing->tipas === 'preke')
                        <div class="text-black mb-4">
                            <strong>Prieinama: </strong>
                            <span
                                class="{{ $listing->kiekis == 0 ? 'font-bold' : '' }}"
                                style="{{ $listing->kiekis == 0 ? 'color: rgb(184, 80, 54)' : '' }}">
                                {{ $listing->kiekis }}
                            </span>
                        </div>
                    @endif

                    @if ($listing->is_renewable)
                        <div class="mb-4">
                            <span
                                class="inline-block px-3 py-1 rounded text-sm text-white"
                                style="background-color: rgb(131, 99, 84)">
                                Atsinaujinanti prekė – pardavėjas papildo atsargas
                            </span>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-4">
                        <a
                            href="{{ route('listing.edit', ['listing' => $listing->id, 'back' => route('admin.reported-listings.show', $listing->id)]) }}"
                            class="px-6 py-3 text-white rounded hover:text-black transition text-center w-full sm:w-40 whitespace-nowrap"
                            style="background-color: rgb(131, 99, 84)">
                            Redaguoti
                        </a>
                    </div>

                    <div class="mt-8 sm:mt-10 border-t pt-6" style="border-color: #836354">
                        <h3 class="font-semibold text-black mb-2">Moderavimo informacija</h3>

                        <div
                            class="p-4 rounded border text-sm"
                            style="background-color: rgb(234, 220, 200); border-color: #836354">
                            <div class="text-black font-semibold text-base sm:text-lg">
                                {{ $seller->vardas }} {{ $seller->pavarde }}
                            </div>

                            <div class="text-black mt-2">
                                Pranešimai apie skelbimus: {{ $listingReportsCount }}
                            </div>

                            <div class="text-black mt-1">
                                Atsiliepimai: {{ $commentReportsCount }}
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2 mt-4">
                                <a
                                    href="{{ route('admin.reported-listings.user-listings', $seller) }}"
                                    class="px-4 py-2 rounded text-white text-center hover:text-black"
                                    style="background-color: rgb(131, 99, 84)">
                                    Visi pardavėjo skelbimai
                                </a>

                                <a
                                    href="{{ route('admin.reported-listings.user-comments', $seller) }}"
                                    class="px-4 py-2 rounded text-white text-center hover:text-black"
                                    style="background-color: rgb(131, 99, 84)">
                                    Visi komentarai
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <form
                            method="POST"
                            action="{{ route('admin.reported-listings.remove', $listing) }}"
                            onsubmit="return confirm('Ar tikrai norite pašalinti šį skelbimą?');"
                            x-data="{ open: false, removalReason: '{{ old('removal_reason', '') }}' }"
                            @keydown.escape.window="open = false">
                            @csrf

                            <input type="hidden" name="removal_reason" x-bind:value="removalReason">

                            <div class="relative mb-2">
                                <button
                                    type="button"
                                    x-on:click.stop="open = !open"
                                    :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                                    class="w-full rounded border py-2 px-3 text-left focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center text-black"
                                    style="background-color: rgb(234, 220, 200)">
                                    <span x-text="
                                        removalReason === '' ? 'Pasirinkite pašalinimo priežastį' :
                                        removalReason === 'fraud' ? 'Sukčiavimas' :
                                        removalReason === 'fake_item' ? 'Netikra prekė' :
                                        removalReason === 'abuse' ? 'Įžeidžiantis elgesys' :
                                        removalReason === 'spam' ? 'Šlamštas' :
                                        removalReason === 'prohibited_items' ? 'Draudžiamos prekės' :
                                        'Kita'
                                    "></span>

                                    <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div
                                    x-show="open"
                                    x-cloak
                                    x-transition
                                    x-on:click.outside="open = false"
                                    class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50"
                                    style="background-color: rgb(234, 220, 200); border-color: #836354">
                                    <div
                                        x-on:click="removalReason = ''; open = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">
                                        Pasirinkite pašalinimo priežastį
                                    </div>

                                    <div
                                        x-on:click="removalReason = 'fraud'; open = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">
                                        Sukčiavimas
                                    </div>

                                    <div
                                        x-on:click="removalReason = 'fake_item'; open = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">
                                        Netikra prekė
                                    </div>

                                    <div
                                        x-on:click="removalReason = 'abuse'; open = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">
                                        Įžeidžiantis elgesys
                                    </div>

                                    <div
                                        x-on:click="removalReason = 'spam'; open = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">
                                        Šlamštas
                                    </div>

                                    <div
                                        x-on:click="removalReason = 'prohibited_items'; open = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">
                                        Draudžiamos prekės
                                    </div>

                                    <div
                                        x-on:click="removalReason = 'other'; open = false"
                                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">
                                        Kita
                                    </div>
                                </div>
                            </div>

                            <div x-show="removalReason === 'other'" x-cloak>
                                <textarea
                                    name="admin_note"
                                    class="w-full border border-gray-500 rounded p-3 text-black mb-2 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                    rows="3"
                                    style="background-color: rgb(234, 220, 200)"
                                    placeholder="Administratoriaus pastaba. Įrašykite pašalinimo priežastį.">{{ old('admin_note') }}</textarea>
                            </div>

                            <button
                                type="submit"
                                class="px-6 py-3 text-white rounded hover:text-black transition text-center w-full"
                                style="background-color: rgb(184, 80, 54)">
                                Pašalinti skelbimą
                            </button>
                        </form>

                        <form
                            method="POST"
                            action="{{ route('admin.reported-listings.ban-seller', $listing) }}"
                            onsubmit="return confirm('Ar tikrai norite užblokuoti šį naudotoją?');">
                            @csrf

                            <textarea
                                name="admin_note"
                                class="w-full border border-gray-500 rounded p-3 text-black mb-2 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                rows="3"
                                style="background-color: rgb(234, 220, 200)"
                                placeholder="Administratoriaus pastaba"
                                required>{{ old('admin_note') }}</textarea>

                            <button
                                type="submit"
                                class="text-white px-4 py-2 rounded w-full hover:text-black"
                                style="background-color: rgb(184, 80, 54)">
                                Užblokuoti naudotoją
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="text-lg sm:text-xl font-bold mb-4 mt-10 sm:mt-12 text-black">
            Visi šio skelbimo atsiliepimai
        </h3>

        <div class="space-y-4">
            @forelse ($listingReviews as $review)
                <div
                    class="p-4 rounded border"
                    style="background-color: {{ $review->id === $selectedReview->id ? 'rgb(207, 174, 134)' : 'rgb(215, 183, 142)' }}; border-color: #836354">
                    @if ($review->id === $selectedReview->id)
                        <div class="mb-2 text-sm font-semibold" style="color: rgb(184, 80, 54)">
                            Tiriamas komentaras
                        </div>
                    @endif

                    <div class="flex items-center gap-2 mb-1">
                        <strong class="text-black">
                            {{ $review->user->vardas ?? '—' }} {{ $review->user->pavarde ?? '' }}
                        </strong>
                        <span class="text-sm" style="color: rgb(131, 99, 84)">
                            {{ str_repeat('★', $review->ivertinimas) }}
                        </span>
                    </div>

                    <div class="text-sm text-black mb-2">
                        {{ $review->created_at }}
                    </div>

                    <p class="text-black text-sm sm:text-base">
                        {{ $review->komentaras ?: '—' }}
                    </p>

                    @if ($review->id === $selectedReview->id)
                        <form
                            method="POST"
                            action="{{ route('admin.reported-listings.delete-user-comment', ['user' => $user->id, 'review' => $review->id]) }}"
                            onsubmit="return confirm('Ar tikrai norite pašalinti šį komentarą?');"
                            class="mt-3">
                            @csrf
                            @method('DELETE')

                            <textarea
                                name="admin_note"
                                class="w-full border border-gray-500 rounded p-3 text-black mb-2 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                                rows="2"
                                style="background-color: rgb(234, 220, 200)"
                                placeholder="Administratoriaus pastaba"
                                required></textarea>

                            <button
                                type="submit"
                                class="text-white px-4 py-2 rounded hover:text-black"
                                style="background-color: rgb(184, 80, 54)">
                                Pašalinti komentarą
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-black italic">Atsiliepimų dar nėra.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
