<x-app-layout>
    <x-slot name="title">Pranešti komentarai</x-slot>

    <div class="max-w-6xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0">
        <div
            class="rounded-2xl border bg-white shadow-sm overflow-hidden mb-6"
            style="border-color: #d7b78e">
            <div
                class="px-4 sm:px-6 py-4 sm:py-5 border-b"
                style="background-color: rgba(215, 183, 142, 0.18); border-color: #d7b78e">
                <h1
                    class="text-xl sm:text-2xl font-bold"
                    style="color: rgb(67, 50, 43)">
                    @if(($mode ?? null) === 'reported_only')
                        Pranešti atsiliepimai
                    @else
                        {{ $user->vardas }} {{ $user->pavarde }} komentarai
                    @endif
                </h1>
            </div>

            <div class="px-4 sm:px-6 py-4">
                @if(session('success'))
                    <div
                        class="mb-4 rounded-xl border px-4 py-3 text-sm font-medium"
                        style="background-color: rgba(215, 183, 142, 0.18); border-color: #d7b78e; color: rgb(67, 50, 43)">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div
                        class="mb-4 rounded-xl border px-4 py-3"
                        style="background-color: #fff5f5; border-color: #f1b7b7; color: #8f2f2f">
                        <div class="font-semibold mb-2">Nepavyko išsaugoti pakeitimų:</div>
                        <ul class="list-disc pl-5 space-y-1 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-6">
                    <a
                        href="{{ ($mode ?? null) === 'reported_only'
                            ? route('admin.reported-listings.index')
                            : route('admin.reported-listings.index') }}"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium border transition"
                        style="background-color: rgb(131, 99, 84); border-color: #836354; color: #fff">
                        ← Atgal
                    </a>
                </div>

                <div
                    class="rounded-2xl border bg-white shadow-sm overflow-hidden"
                    style="border-color: #d7b78e">
                    <table class="w-full text-sm">
                        <thead
                            class="hidden sm:table-header-group border-b"
                            style="background-color: rgba(215, 183, 142, 0.18); border-color: #d7b78e">
                            <tr>
                                @if(($mode ?? null) === 'reported_only')
                                    <th
                                        class="p-4 text-left font-semibold"
                                        style="color: rgb(67, 50, 43)">
                                        Autorius
                                    </th>
                                @endif
                                <th
                                    class="p-4 text-left font-semibold"
                                    style="color: rgb(67, 50, 43)">
                                    Skelbimas
                                </th>
                                <th
                                    class="p-4 text-left font-semibold"
                                    style="color: rgb(67, 50, 43)">
                                    Įvertinimas
                                </th>
                                <th
                                    class="p-4 text-left font-semibold"
                                    style="color: rgb(67, 50, 43)">
                                    Komentaras
                                </th>
                                <th
                                    class="p-4 text-left font-semibold"
                                    style="color: rgb(67, 50, 43)">
                                    Pranešimai
                                </th>
                                <th
                                    class="p-4 text-left font-semibold"
                                    style="color: rgb(67, 50, 43)">
                                    Data
                                </th>
                                <th
                                    class="p-4 text-left font-semibold"
                                    style="color: rgb(67, 50, 43)">
                                    Veiksmai
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($reviews as $review)
                                <tr
                                    class="block sm:table-row border-b align-top"
                                    style="border-color: #ead8bf">
                                    @if(($mode ?? null) === 'reported_only')
                                        <td class="p-4 block sm:table-cell">
                                            <span
                                                class="font-semibold sm:hidden"
                                                style="color: rgb(67, 50, 43)">
                                                Autorius:
                                            </span>
                                            <span style="color: rgb(67, 50, 43)">
                                                {{ $review->user->vardas ?? '—' }} {{ $review->user->pavarde ?? '' }}
                                            </span>
                                        </td>
                                    @endif

                                    <td class="p-4 block sm:table-cell">
                                        <span
                                            class="font-semibold sm:hidden"
                                            style="color: rgb(67, 50, 43)">
                                            Skelbimas:
                                        </span>
                                        <span style="color: rgb(67, 50, 43)">
                                            {{ $review->Listing->pavadinimas ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="p-4 block sm:table-cell">
                                        <span
                                            class="font-semibold sm:hidden"
                                            style="color: rgb(67, 50, 43)">
                                            Įvertinimas:
                                        </span>
                                        <span style="color: rgb(67, 50, 43)">
                                            {{ $review->ivertinimas }}
                                        </span>
                                    </td>

                                    <td class="p-4 block sm:table-cell">
                                        <span
                                            class="font-semibold sm:hidden"
                                            style="color: rgb(67, 50, 43)">
                                            Komentaras:
                                        </span>
                                        <span
                                            class="block leading-relaxed"
                                            style="color: rgb(67, 50, 43)">
                                            {{ $review->komentaras ?: '—' }}
                                        </span>
                                    </td>

                                    <td class="p-4 block sm:table-cell">
                                        <span
                                            class="font-semibold sm:hidden"
                                            style="color: rgb(67, 50, 43)">
                                            Pranešimai:
                                        </span>
                                        @if(($review->pending_reports_count ?? 0) > 0)
                                            <span
                                                class="font-semibold"
                                                style="color: #9a3d33">
                                                {{ $review->pending_reports_count }}
                                            </span>
                                        @else
                                            <span style="color: #8b7768">0</span>
                                        @endif
                                    </td>

                                    <td class="p-4 block sm:table-cell">
                                        <span
                                            class="font-semibold sm:hidden"
                                            style="color: rgb(67, 50, 43)">
                                            Data:
                                        </span>
                                        <span style="color: rgb(67, 50, 43)">
                                            {{ $review->created_at }}
                                        </span>
                                    </td>

                                    <td class="p-4 block sm:table-cell">
                                        <div class="flex flex-col gap-2 min-w-[220px]">
                                            <a
                                                href="{{ route('admin.reported-listings.compare-user-comment', [
                                                    'user' => $review->user_id,
                                                    'review' => $review->id
                                                ]) }}"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border text-center transition"
                                                style="background-color: rgb(215, 183, 142); border-color: #836354; color: rgb(67, 50, 43)">
                                                Peržiūrėti visus atsiliepimus
                                            </a>

                                            @if($review->Listing)
                                                <a
                                                    href="{{ route('admin.reported-listings.show', [
                                                        'listing' => $review->Listing->id,
                                                        'back' => request()->fullUrl()
                                                    ]) }}"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border text-center transition"
                                                    style="background-color: rgb(131, 99, 84); border-color: #836354; color: #fff">
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
                                                class="rounded-xl border p-3"
                                                style="border-color: #ead8bf; background-color: #fcfaf7">
                                                @csrf
                                                @method('DELETE')

                                                <textarea
                                                    name="admin_note"
                                                    class="w-full rounded-md border px-3 py-2 text-sm mb-2 resize-y"
                                                    rows="2"
                                                    placeholder="Administratoriaus pastaba"
                                                    style="border-color: #d7b78e; color: rgb(67, 50, 43); background-color: #fff"
                                                    required>{{ old('admin_note') }}</textarea>

                                                <button
                                                    type="submit"
                                                    class="w-full inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border transition"
                                                    style="background-color: #9a5c52; border-color: #8b4f46; color: #fff">
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
                                        class="p-6 text-center text-sm"
                                        style="color: #8b7768; background-color: #fcfaf7">
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
        </div>
    </div>
</x-app-layout>
