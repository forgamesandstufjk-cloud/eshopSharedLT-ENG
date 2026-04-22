<x-app-layout>
    <x-slot name="title">Pranešti komentarai</x-slot>

    <div class="min-h-screen w-full max-w-6xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0" style="background-color: rgb(234, 220, 200)">
        <h1 class="text-xl sm:text-2xl font-bold mb-2 text-black">
            @if(($mode ?? null) === 'reported_only')
                Pranešti atsiliepimai
            @else
                {{ $user->vardas }} {{ $user->pavarde }} komentarai
            @endif
        </h1>

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

        @if($errors->any())
            <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(230, 190, 190)">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="mb-6">
            <a
                href="{{ ($mode ?? null) === 'reported_only'
                    ? route('admin.reported-listings.index')
                    : route('admin.reported-listings.index') }}"
                class="inline-block px-4 py-2 rounded text-white hover:text-black"
                style="background-color: rgb(131, 99, 84)">
                ← Atgal
            </a>
        </div>

        <div class="shadow rounded overflow-hidden" style="background-color: rgb(215, 183, 142)">
            <table class="w-full text-sm text-black">
                <thead class="border-b hidden sm:table-header-group" style="background-color: rgb(131, 99, 84); border-color: #836354">
                    <tr>
                        @if(($mode ?? null) === 'reported_only')
                            <th class="p-3 text-left text-white">Autorius</th>
                        @endif
                        <th class="p-3 text-left text-white">Skelbimas</th>
                        <th class="p-3 text-left text-white">Įvertinimas</th>
                        <th class="p-3 text-left text-white">Komentaras</th>
                        <th class="p-3 text-left text-white">Pranešimai</th>
                        <th class="p-3 text-left text-white">Data</th>
                        <th class="p-3 text-left text-white">Veiksmai</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reviews as $review)
                        <tr class="border-b block sm:table-row align-top" style="border-color: #836354">
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
                                    <span style="color: rgb(184, 80, 54)" class="font-semibold">
                                        {{ $review->pending_reports_count }}
                                    </span>
                                @else
                                    <span class="text-black">0</span>
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
                                        class="inline-block text-white px-3 py-2 rounded text-center hover:text-black"
                                        style="background-color: rgb(131, 99, 84)">
                                        Peržiūrėti visus atsiliepimus
                                    </a>

                                    @if($review->Listing)
                                        <a
                                            href="{{ route('admin.reported-listings.show', [
                                                'listing' => $review->Listing->id,
                                                'back' => request()->fullUrl()
                                            ]) }}"
                                            class="inline-block px-3 py-2 rounded text-white text-center hover:text-black"
                                            style="background-color: rgb(131, 99, 84)">
                                            Peržiūrėti skelbimą
                                        </a>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('admin.reported-listings.delete-user-comment', [
                                            'user' => $review->user_id,
                                            'review' => $review->id
                                        ]) }}"
                                        onsubmit="return confirm('Ar tikrai norite pašalinti šį komentarą?');">
                                        @csrf
                                        @method('DELETE')

                                        <textarea
                                            name="admin_note"
                                            class="border p-2 rounded w-full mb-2 text-black"
                                            rows="2"
                                            placeholder="Administratoriaus pastaba"
                                            style="background-color: rgb(234, 220, 200); border-color: #836354"
                                            required>{{ old('admin_note') }}</textarea>

                                        <button
                                            type="submit"
                                            class="w-full text-white px-3 py-2 rounded hover:text-black"
                                            style="background-color: rgb(184, 80, 54)">
                                            Pašalinti komentarą
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="{{ ($mode ?? null) === 'reported_only' ? '7' : '6' }}"
                                class="p-4 text-center text-black">
                                Komentarų nerasta.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-black">
            {{ $reviews->links() }}
        </div>
    </div>
</x-app-layout>
