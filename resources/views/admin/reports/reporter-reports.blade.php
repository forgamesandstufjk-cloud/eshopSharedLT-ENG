<x-app-layout>
    <div class="max-w-6xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0">
        <h1 class="text-xl sm:text-2xl font-bold mb-2">
            {{ $user->vardas }} {{ $user->pavarde }} pateikti pranešimai
        </h1>

        <div class="mb-6">
            <a
                href="{{ url()->previous() }}"
                class="inline-block px-4 py-2 rounded text-white"
                style="background-color: rgb(131, 99, 84)"
            >
                ← Atgal
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="p-4 rounded shadow" style="background-color: rgb(215, 183, 142)">
                <div class="text-sm text-black">Viso pranešimų</div>
                <div class="text-2xl font-bold text-black">{{ $stats['total'] }}</div>
            </div>

            <div class="p-4 rounded shadow" style="background-color: rgb(215, 183, 142)">
                <div class="text-sm text-black">Laukiama</div>
                <div class="text-2xl font-bold text-black">{{ $stats['pending'] }}</div>
            </div>

            <div class="p-4 rounded shadow" style="background-color: rgb(215, 183, 142)">
                <div class="text-sm text-black">Atmesta</div>
                <div class="text-2xl font-bold" style="color: rgb(184, 80, 54)">
                    {{ $stats['dismissed'] }}
                </div>
            </div>

            <div class="p-4 rounded shadow" style="background-color: rgb(215, 183, 142)">
                <div class="text-sm text-black">Išspręsta</div>
                <div class="text-2xl font-bold text-black">{{ $stats['resolved'] }}</div>
            </div>
        </div>

        @if($stats['total'] > 0)
            @php
                $dismissedRate = round(($stats['dismissed'] / $stats['total']) * 100);
            @endphp

            <div class="mb-6 p-4 rounded border text-sm"
                 style="background-color: rgb(234, 220, 200); border-color: #836354">
                <div class="text-black">
                    Atmestų pranešimų dalis:
                    <strong>{{ $dismissedRate }}%</strong>
                </div>

                @if($dismissedRate >= 70 && $stats['total'] >= 5)
                    <div class="mt-2 font-semibold" style="color: rgb(184, 80, 54)">
                        Šis naudotojas turi daug atmestų pranešimų. Verta patikrinti dėl galimo piktnaudžiavimo pranešimų sistema.
                    </div>
                @endif
            </div>
        @endif

        <div class="mb-6 p-4 rounded border"
     style="background-color: rgb(234, 220, 200); border-color: #836354">
    <div class="text-black font-semibold text-base mb-3">
        Naudotojo valdymas
    </div>

    @if($user->is_banned)
        <div class="text-sm font-semibold mb-3" style="color: rgb(184, 80, 54)">
            Naudotojas užblokuotas
        </div>

        <form method="POST"
              action="{{ route('admin.reported-listings.unban-seller', $user) }}"
              onsubmit="return confirm('Ar tikrai norite atblokuoti šį naudotoją?');">
            @csrf

            <button
                type="submit"
                class="px-4 py-2 rounded text-white"
                style="background-color: rgb(131, 99, 84)"
            >
                Atblokuoti naudotoją
            </button>
        </form>
    @else
        <form method="POST"
              action="{{ route('admin.reported-listings.ban-reporter', $user) }}"
              onsubmit="return confirm('Ar tikrai norite užblokuoti šį naudotoją dėl galimo piktnaudžiavimo pranešimų sistema?');">
            @csrf

            <textarea
                name="admin_note"
                class="border p-2 rounded w-full mb-3"
                rows="3"
                placeholder="Administratoriaus pastaba"
                required
            ></textarea>

            <button
                type="submit"
                class="px-4 py-2 rounded text-white bg-red-600"
            >
                Užblokuoti naudotoją
            </button>
        </form>
    @endif
</div>

        
        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b hidden sm:table-header-group">
                    <tr>
                        <th class="p-3 text-left">Skelbimas</th>
                        <th class="p-3 text-left">Apie ką pranešė</th>
                        <th class="p-3 text-left">Priežastis</th>
                        <th class="p-3 text-left">Detalės</th>
                        <th class="p-3 text-left">Būsena</th>
                        <th class="p-3 text-left">Data</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reports as $report)
                        <tr class="border-b block sm:table-row align-top">
                            <td class="p-3 block sm:table-cell">
                                @if($report->listing)
                                    <div>{{ $report->listing->pavadinimas }}</div>

                                    @if($report->listing->photos && $report->listing->photos->isNotEmpty())
                                        <img
    src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($report->listing->photos->first()->failo_url) }}"
    class="w-16 h-16 object-cover rounded mt-2 border"
>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>

                            <td class="p-3 block sm:table-cell">
                                {{ $report->reportedUser->vardas ?? '—' }} {{ $report->reportedUser->pavarde ?? '' }}
                            </td>

                            <td class="p-3 block sm:table-cell">
                                @switch($report->reason)
                                    @case('fraud') Sukčiavimas @break
                                    @case('fake_item') Netikra prekė @break
                                    @case('abuse') Įžeidžiantis tekstas @break
                                    @case('spam') Nepadorus turinys @break
                                    @case('prohibited_items') Draudžiamos prekės @break
                                    @case('other') Kita @break
                                    @default {{ $report->reason }}
                                @endswitch
                            </td>

                            <td class="p-3 block sm:table-cell">
                                {{ $report->details ?: '—' }}
                            </td>

                            <td class="p-3 block sm:table-cell">
                                @if($report->status === 'pending')
                                    Laukiama
                                @elseif($report->status === 'dismissed')
                                    Atmesta
                                @elseif($report->status === 'resolved')
                                    Išspręsta
                                @else
                                    {{ $report->status }}
                                @endif
                            </td>

                            <td class="p-3 block sm:table-cell">
                                {{ $report->created_at }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">
                                Šis naudotojas nėra pateikęs pranešimų.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    </div>
</x-app-layout>
