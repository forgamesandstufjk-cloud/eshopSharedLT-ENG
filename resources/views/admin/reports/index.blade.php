<x-app-layout>
    <div class="max-w-7xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0">
        <h1 class="text-xl sm:text-2xl font-bold mb-6">Pranešti skelbimai</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b hidden sm:table-header-group">
                    <tr>
                        <th class="p-3 text-left">Skelbimas</th>
                        <th class="p-3 text-left">Pardavėjas</th>
                        <th class="p-3 text-left">Pranešimų kiekis</th>
                        <th class="p-3 text-left">Veiksmai</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($listings as $listing)
                    <tr class="border-b block sm:table-row align-top">
                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Skelbimas: </span>

                            <div>{{ $listing->pavadinimas ?? '—' }}</div>

                            @if($listing->photos && $listing->photos->isNotEmpty())
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($listing->photos->first()->failo_url) }}"
                                    class="w-16 h-16 object-cover rounded mt-2 border"
                                >
                            @endif
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Pardavėjas: </span>
                            {{ $listing->user->vardas ?? '—' }} {{ $listing->user->pavarde ?? '' }}

                            <div class="text-xs text-gray-500 mt-1">
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
                               class="inline-block bg-blue-600 text-white px-3 py-1 rounded">
                                Peržiūrėti
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            Praneštų skelbimų nėra.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
