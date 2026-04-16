<x-app-layout>
<style>
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}
</style>

<div class="max-w-6xl mx-auto py-6 sm:py-10 px-3 sm:px-4" style="background-color: rgb(234, 220, 200)">

    @if(session('success'))
        <div class="mb-6 px-0 sm:px-4">
            <div class="px-4 py-3 rounded text-black" style="background-color: rgb(207, 174, 134); border: 1px solid #836354">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 px-0 sm:px-4">
            <div class="px-4 py-3 rounded text-black" style="background-color: rgb(207, 174, 134); border: 1px solid #836354">
                {{ $errors->first() }}
            </div>
        </div>
    @endif
<div class="mb-4">  
    <a href="{{ request('back') ?: route('admin.reported-listings.index') }}"
       class="inline-block px-4 py-2 rounded text-white"
       style="background-color: rgb(131, 99, 84)">
        ← Atgal
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
    class="rounded-lg shadow w-full max-h-[320px] sm:max-h-[450px] object-cover mb-4"
/>

                @if($listing->photos->count() > 1)
                    <div class="flex gap-2 sm:gap-3 overflow-x-auto">
                        @foreach($listing->photos as $photo)
                            <img
    src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($photo->failo_url) }}"
    class="w-16 h-16 sm:w-20 sm:h-20 rounded object-cover cursor-pointer border"
    style="border-color: #836354;"
    onclick="document.getElementById('mainImage').src=this.src"
>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex flex-col">
                <div class="mb-3">
                    <span class="inline-block px-3 py-1 rounded text-sm text-white"
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
                        @if($listing->tipas === 'preke') / vnt @else / paslauga @endif
                    </span>
                </div>

                @if($listing->tipas === 'preke')
                    <div class="text-black mb-4">
                        <strong>Prieinama: </strong>
                        <span class="{{ $listing->kiekis == 0 ? 'font-bold' : '' }}"
                              style="{{ $listing->kiekis == 0 ? 'color: rgb(184, 80, 54);' : '' }}">
                            {{ $listing->kiekis }}
                        </span>
                    </div>
                @endif

                @if($listing->is_renewable)
                    <div class="mb-4">
                        <span class="inline-block px-3 py-1 rounded text-sm text-black"
                              style="background-color: rgb(131, 99, 84)">
                            Atsinaujinanti prekė – pardavėjas papildo atsargas
                        </span>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-4">
                    <a href="{{ route('listing.edit', ['listing' => $listing->id, 'back' => route('admin.reported-listings.show', $listing->id)]) }}"
                       class="px-6 py-3 text-white rounded hover:text-black transition text-center w-full sm:w-40 whitespace-nowrap"
                       style="background-color: rgb(131, 99, 84)">
                        Redaguoti
                    </a>
                </div>

                <div class="mt-8 sm:mt-10 border-t pt-6" style="border-color: #836354">
                    <h3 class="font-semibold text-black mb-2">Moderavimo informacija</h3>

                    <div class="p-4 rounded border text-sm"
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
                            <a href="{{ route('admin.reported-listings.user-listings', $seller) }}"
                               class="px-4 py-2 rounded text-white text-center"
                               style="background-color: rgb(131, 99, 84)">
                                Visi pardavėjo skelbimai
                            </a>

                            <a href="{{ route('admin.reported-listings.user-comments', $seller) }}"
                               class="px-4 py-2 rounded text-white text-center"
                               style="background-color: rgb(131, 99, 84)">
                                Visi atsiliepimai
                            </a>
                        </div>
                    </div>
                </div>
<div class="mt-6 space-y-3">
    <form method="POST"
          action="{{ route('admin.reported-listings.remove', $listing) }}"
          onsubmit="return confirm('Ar tikrai norite pašalinti šį skelbimą?');"
          x-data="{ removalReason: '' }">
        @csrf

        <select name="removal_reason"
                x-model="removalReason"
                class="border p-2 rounded w-full mb-2"
                required>
            <option value="">Pasirinkite pašalinimo priežastį</option>
            <option value="fraud">Sukčiavimas</option>
            <option value="fake_item">Netikra prekė</option>
            <option value="abuse">Įžeidžiantis elgesys</option>
            <option value="spam">Šlamštas</option>
            <option value="prohibited_items">Draudžiamos prekės</option>
            <option value="other">Kita</option>
        </select>

        <div x-show="removalReason === 'other'" x-cloak>
            <textarea
                name="admin_note"
                class="border p-2 rounded w-full mb-2"
                rows="3"
                placeholder="Administratoriaus pastaba. Įrašykite pašalinimo priežastį."
            ></textarea>
        </div>

        <button type="submit"
            class="px-6 py-3 text-white rounded hover:text-black transition text-center w-full"
            style="background-color: rgb(184, 80, 54)">
            Pašalinti skelbimą
        </button>
    </form>

    @if($seller->is_banned)
        <form method="POST"
              action="{{ route('admin.reported-listings.unban-seller', $seller) }}"
              onsubmit="return confirm('Ar tikrai norite atblokuoti šį naudotoją?');">
            @csrf

            <button
                type="submit"
                class="w-full px-4 py-2 rounded text-white"
                style="background-color: rgb(131, 99, 84)"
            >
                Atblokuoti naudotoją
            </button>
        </form>
    @else
        <form method="POST"
              action="{{ route('admin.reported-listings.ban-seller', $listing) }}"
              onsubmit="return confirm('Ar tikrai norite užblokuoti šį naudotoją?');">
            @csrf

            <textarea
                name="admin_note"
                class="border p-2 rounded w-full mb-2"
                rows="3"
                placeholder="Administratoriaus pastaba"
                required
            ></textarea>

            <button class="bg-red-600 text-white px-3 py-2 rounded w-full">
                Užblokuoti naudotoją
            </button>
        </form>
    @endif
</div>                
            </div>
        </div>
    </div>

    <section class="mt-12 sm:mt-16">
        <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-black">Pranešimai apie šį skelbimą</h3>

        <div class="space-y-4">
            @forelse($reportsByReason as $reason => $reports)
                <div class="p-4 rounded border" style="background-color: rgb(215, 183, 142); border-color: #836354;">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                        <div>
                            <div class="font-semibold text-black">
                                Priežastis:
                                @switch($reason)
                                    @case('fraud') Sukčiavimas @break
                                    @case('fake_item') Netikra prekė @break
                                    @case('abuse') Įžeidžiantis tekstas @break
                                    @case('spam') Nepadorus turinys @break
                                    @case('prohibited_items') Draudžiamos prekės @break
                                    @case('other') Kita @break
                                    @default {{ $reason }}
                                @endswitch
                            </div>

                            <div class="text-sm text-black mt-1">
                                Viso pranešimų: {{ $reports->count() }}
                            </div>
                        </div>

                        @if($reports->where('status', 'pending')->count() > 0)
                            <form method="POST" action="{{ route('admin.reported-listings.dismiss-reason', $listing) }}" class="w-full sm:w-auto">
                                @csrf
                                <input type="hidden" name="reason" value="{{ $reason }}">

                                <textarea
                                    name="admin_note"
                                    class="border p-2 rounded w-full mb-2"
                                    rows="2"
                                    placeholder="Administratoriaus pastaba"
                                ></textarea>

                                <button class="bg-gray-500 text-white px-3 py-2 rounded w-full">
                                    Atmesti šios priežasties pranešimus
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @foreach($reports as $report)
                            <div class="p-4 rounded border bg-white" style="border-color: #836354;">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <strong class="text-black">
                                        {{ $report->reporterUser->vardas ?? '—' }} {{ $report->reporterUser->pavarde ?? '' }}
                                    </strong>

                                    <span class="text-sm text-black">
                                        @if($report->status === 'pending')
                                            Laukiama
                                        @elseif($report->status === 'dismissed')
                                            Atmesta
                                        @elseif($report->status === 'resolved')
                                            Išspręsta
                                        @else
                                            {{ $report->status }}
                                        @endif
                                    </span>
                                </div>

                                <div class="text-sm text-black">
                                    {{ $report->details ?: '—' }}
                                </div>

                                    <a
                                        href="{{ route('admin.reported-listings.reporter-reports', $report->reporterUser->id) }}"
                                        class="inline-block mt-3 px-3 py-2 rounded text-white text-sm"
                                        style="background-color: rgb(131, 99, 84)"
                                    >
                                        Visi šio naudotojo pranešimai
                                    </a>

                                @if($report->admin_note)
                                    <div class="text-sm text-gray-600 mt-2">
                                        Administratoriaus pastaba: {{ $report->admin_note }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-black italic">Pranešimų dar nėra.</p>
            @endforelse
        </div>
    </section>
</div>
</x-app-layout>
