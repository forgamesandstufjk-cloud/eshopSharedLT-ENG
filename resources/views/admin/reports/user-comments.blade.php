<x-app-layout>
    <div class="max-w-6xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0">
        <h1 class="text-xl sm:text-2xl font-bold mb-2">
            @if(($mode ?? null) === 'reported_only')
                Pranešti atsiliepimai
            @else
                {{ $user->vardas }} {{ $user->pavarde }} komentarai
            @endif
        </h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6">
            <a
                href="{{ ($mode ?? null) === 'reported_only'
                    ? route('admin.reported-listings.index')
                    : route('admin.reported-listings.index') }}"
                class="inline-block px-4 py-2 rounded text-white"
                style="background-color: rgb(131, 99, 84)"
            >
                ← Atgal
            </a>
        </div>

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b hidden sm:table-header-group">
                    <tr>
                        @if(($mode ?? null) === 'reported_only')
                            <th class="p-3 text-left">Autorius</th>
                        @endif
                        <th class="p-3 text-left">Skelbimas</th>
                        <th class="p-3 text-left">Įvertinimas</th>
                        <th class="p-3 text-left">Komentaras</th>
                        <th class="p-3 text-left">Pranešimai</th>
                        <th class="p-3 text-left">Data</th>
                        <th class="p-3 text-left">Veiksmai</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reviews as $review)
                        <tr class="border-b block sm:table-row align-top">
                            @if(($mode ?? null) === 'reported_only')
                                <td class="p-3 block sm:table-cell">
                                    <span class="font-semibold sm:hidden">Autorius: </span>
                                    {{ $review->user->vardas ?? '—' }} {{ $review->user->pavarde ?? '' }}
                                </td>
                            @endif

                            <td class="p-3 block sm:table-cell">
                                <span class="font-semibold sm:hidden">Skelbimas: </span>
                                {{ $review->Listing->pavadinimas ?? '—' }}
                            </td>

                            <td class="p-3 block sm:table-cell">
                                <span class="font-semibold sm:hidden">Įvertinimas: </span>
                                {{ $review->ivertinimas }}
                            </td>

                            <td class="p-3 block sm:table-cell">
                                <span class="font-semibold sm:hidden">Komentaras: </span>
                                {{ $review->komentaras ?: '—' }}
                            </td>

                            <td class="p-3 block sm:table-cell">
                                <span class="font-semibold sm:hidden">Pranešimai: </span>
                                @if(($review->pending_reports_count ?? 0) > 0)
                                    <span class="text-red-600 font-semibold">
                                        {{ $review->pending_reports_count }}
                                    </span>
                                @else
                                    <span class="text-gray-500">0</span>
                                @endif
                            </td>

                            <td class="p-3 block sm:table-cell">
                                <span class="font-semibold sm:hidden">Data: </span>
                                {{ $review->created_at }}
                            </td>

                            <td class="p-3 block sm:table-cell">
                                <div class="flex flex-col gap-2">
                                    <a
                                        href="{{ route('admin.reported-listings.compare-user-comment', [
                                            'user' => $review->user_id,
                                            'review' => $review->id
                                        ]) }}"
                                        class="inline-block bg-blue-600 text-white px-3 py-2 rounded text-center"
                                    >
                                        Peržiūrėti visus atsiliepimus
                                    </a>

                                    @if($review->Listing)
                                        <a
                                            href="{{ route('admin.reported-listings.show', [
                                                'listing' => $review->Listing->id,
                                                'back' => request()->fullUrl()
                                            ]) }}"
                                            class="inline-block px-3 py-2 rounded text-white text-center"
                                            style="background-color: rgb(131, 99, 84)"
                                        >
                                            Peržiūrėti skelbimą
                                        </a>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('admin.reported-listings.delete-user-comment', [
                                            'user' => $review->user_id,
                                            'review' => $review->id
                                        ]) }}"
                                        onsubmit="return confirm('Ar tikrai norite pašalinti šį komentarą?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <textarea
                                            name="admin_note"
                                            class="border p-2 rounded w-full mb-2"
                                            rows="2"
                                            placeholder="Administratoriaus pastaba"
                                            required
                                        ></textarea>

                                        <button
                                            type="submit"
                                            class="w-full bg-red-600 text-white px-3 py-2 rounded"
                                        >
                                            Pašalinti komentarą
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($mode ?? null) === 'reported_only' ? '7' : '6' }}"
                                class="p-4 text-center text-gray-500">
                                Komentarų nerasta.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    </div>
</x-app-layout>
