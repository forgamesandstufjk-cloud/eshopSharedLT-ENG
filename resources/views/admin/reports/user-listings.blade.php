<x-app-layout>
     <x-slot name="title">Praneštas skelbimas</x-slot>
    <div
        x-data
        x-init="Alpine.store('favorites').load()"
        class="min-h-screen w-full px-3 sm:px-4 mt-6 sm:mt-8"
        style="background-color: rgb(234, 220, 200)"
    >
        <div
            class="fixed inset-0 pointer-events-none bg-no-repeat bg-center bg-contain"
            style="background-image: url('{{ asset('images/vytis.png') }}'); background-size: 500px 500px; background-position: center calc(50% + 60px); opacity: 0.3;"
        ></div>

        <div class="container mx-auto relative z-10">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-black">
                    {{ $user->vardas }} {{ $user->pavarde }} skelbimai
                </h1>

                <div class="text-sm text-black mt-1">
                    El. paštas: {{ $user->el_pastas }}
                </div>

                @if($user->is_banned)
    <div class="text-sm font-semibold mt-1" style="color: rgb(184, 80, 54)">
        Naudotojas užblokuotas
    </div>

    <form method="POST"
          action="{{ route('admin.reported-listings.unban-seller', $user) }}"
          onsubmit="return confirm('Ar tikrai norite atblokuoti šį naudotoją?');"
          class="mt-3">
        @csrf

        <button
            type="submit"
            class="inline-block px-4 py-2 rounded text-white"
            style="background-color: rgb(131, 99, 84)"
        >
            Atblokuoti naudotoją
        </button>
    </form>
@endif

                <div class="mt-3">
                    <a
                        href="{{ route('admin.reported-listings.index') }}"
                        class="inline-block px-4 py-2 rounded text-white"
                        style="background-color: rgb(131, 99, 84)"
                    >
                        ← Atgal į praneštus skelbimus
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-[repeat(auto-fit,minmax(260px,320px))] gap-4 sm:gap-6 justify-center">
                @forelse ($listings as $item)
                    <div class="shadow rounded overflow-hidden hover:shadow-lg transition flex flex-col"
                         style="background-color: rgb(215, 183, 142)">

                        <div class="relative">
                            @if($item->photos->isNotEmpty())
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($item->photos->first()->failo_url) }}"
                                    class="w-full h-44 sm:h-48 object-cover"
                                >
                            @else
                                <img
                                    src="https://via.placeholder.com/300"
                                    class="w-full h-44 sm:h-48 object-cover"
                                >
                            @endif

                            @if(($item->reports_count ?? 0) > 0)
                                <div class="absolute top-2 left-2 px-2 py-1 rounded text-xs text-white"
                                     style="background-color: rgb(184, 80, 54)">
                                    Praneštas
                                </div>
                            @else
                                <div class="absolute top-2 left-2 px-2 py-1 rounded text-xs text-white"
                                     style="background-color: rgb(131, 99, 84)">
                                    Be pranešimų
                                </div>
                            @endif
                        </div>

                        <div class="p-3 sm:p-4 flex flex-col flex-1">
                            <h2 class="text-base sm:text-lg font-semibold mb-1 leading-snug text-black">
                                {{ $item->pavadinimas }}
                            </h2>

                            <p class="text-black text-sm line-clamp-2 flex-1">
                                {{ $item->aprasymas }}
                            </p>

                            <div class="text-sm text-black mt-2">
                                Pranešimų: {{ $item->reports_count ?? 0 }}
                            </div>

                            <div class="flex justify-between items-center mt-3">
                                <span class="font-bold text-base sm:text-lg" style="color: rgb(131, 99, 84)">
                                    {{ $item->kaina }} €
                                </span>

                                <a
                                    href="{{ route('admin.reported-listings.show', ['listing' => $item->id, 'back' => request()->fullUrl()]) }}"
                                    class="font-semibold text-sm sm:text-base text-black hover:underline"
                                >
                                    Plačiau →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-black text-center col-span-full">
                        Šis naudotojas neturi skelbimų.
                    </p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $listings->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
