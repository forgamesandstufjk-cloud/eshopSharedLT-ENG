<x-app-layout>
    <x-slot name="title">Pranešti skelbimai</x-slot>

    <div class="min-h-screen max-w-7xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0 pb-10" style="background-color: rgb(234, 220, 200)">
        <h1 class="text-xl sm:text-2xl font-bold mb-6 text-black">Pranešti skelbimai</h1>

        @if(session('success'))
            <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(207, 174, 134)">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(230, 190, 190)">
                {{ session('error') }}
            </div>
        @endif

        <div class="shadow rounded overflow-hidden" style="background-color: rgb(215, 183, 142)">
            <table class="w-full text-sm text-black">
                <thead class="border-b hidden sm:table-header-group" style="background-color: rgb(131, 99, 84); border-color: #836354">
                    <tr>
                        <th class="p-3 text-left text-white">Skelbimas</th>
                        <th class="p-3 text-left text-white">Pardavėjas</th>
                        <th class="p-3 text-left text-white">Pranešimų kiekis</th>
                        <th class="p-3 text-left text-white">Veiksmai</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($listings as $listing)
                        <tr class="border-b block sm:table-row align-top" style="border-color: #836354">
                            <td class="p-3 block sm:table-cell">
                                <span class="font-semibold sm:hidden">Skelbimas: </span>

                                <div>{{ $listing->pavadinimas ?? '—' }}</div>

                                @if($listing->photos && $listing->photos->isNotEmpty())
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($listing->photos->first()->failo_url) }}"
                                        class="w-16 h-16 object-cover rounded mt-2 border"
                                        style="border-color: #836354">
                                @endif
                            </td>

                            <td class="p-3 block sm:table-cell">
                                <span class="font-semibold sm:hidden">Pardavėjas: </span>
                                {{ $listing->user->vardas ?? '—' }} {{ $listing->user->pavarde ?? '' }}

                                <div class="text-xs text-black mt-1">
                                    ID: {{ $listing->user_id }}
                                    @if($listing->user?->is_banned)
                                        | Užblokuotas
                                    @endif
                                </div>
                            </td>

                            <td class="p-3 block sm:table-cell">
                                <span class="font-semibold sm:hidden">Pranešimų kiekis: </span>
                                {{ $listing->reports_count }}
                            </td>

                            <td class="p-3 block sm:table-cell">
                                <a href="{{ route('admin.reported-listings.show', $listing) }}"
                                   class="inline-block text-white px-3 py-1 rounded hover:text-black"
                                   style="background-color: rgb(131, 99, 84)">
                                    Peržiūrėti
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-black">
                                Praneštų skelbimų nėra.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
