<x-app-layout>

    <div class="max-w-4xl mx-auto mt-10 shadow p-6 rounded"
         style="background-color: rgb(215, 183, 142)"
         x-data="{ type: '{{ old('tipas', $listing->tipas) }}' }">

        <h1 class="text-2xl font-bold mb-6 text-black">Redaguoti skelbimą</h1>

        @if(session('success'))
            <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(207, 174, 134)">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(207, 174, 134)">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(207, 174, 134)">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('listing.update', $listing->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- TITLE --}}
            <div class="mb-4">
                <label class="font-semibold text-black">Pavadinimas</label>
                <input 
                    type="text" 
                    name="pavadinimas"
                    value="{{ old('pavadinimas', $listing->pavadinimas) }}"
                    class="w-full border border-gray-500 rounded px-3 py-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                    style="background-color: rgb(234, 220, 200)"
                    required>
            </div>

            {{-- DESCRIPTION --}}
            <div class="mb-4">
                <label class="font-semibold text-black">Aprašymas</label>
                <textarea 
                    name="aprasymas" 
                    rows="5"
                    class="w-full border border-gray-500 rounded px-3 py-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                    style="background-color: rgb(234, 220, 200)"
                    required>{{ old('aprasymas', $listing->aprasymas) }}</textarea>
            </div>

            {{-- PRICE --}}
            <div class="mb-4">
                <label class="font-semibold text-black">Kaina (€)</label>
                <input 
                    type="number" 
                    min="0"
                    step="0.01" 
                    name="kaina"
                    value="{{ old('kaina', $listing->kaina) }}"
                    class="w-full border border-gray-500 rounded px-3 py-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                    style="background-color: rgb(234, 220, 200)"
                    required>
            </div>

            {{-- TYPE --}}
            <div class="mb-4 relative" x-data="{ open: false }">
                <label class="font-semibold block text-black">Tipas</label>

                <input type="hidden" name="tipas" x-model="type" required>

                <button
                    type="button"
                    @click="open = !open"
                    :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                    class="w-full rounded border py-2 px-3 text-left focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                    style="background-color: rgb(234, 220, 200)"
                >
                    <span class="text-black" x-text="type === 'preke' ? 'Prekė' : 'Paslauga'"></span>

                    <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    @click.outside="open = false"
                    class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50"
                    style="background-color: rgb(234, 220, 200); border-color: #836354"
                >
                    <div
                        @click="type = 'preke'; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        Prekė
                    </div>

                    <div
                        @click="type = 'paslauga'; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        Paslauga
                    </div>
                </div>
            </div>

            {{-- CATEGORY --}}
                <div
                    class="mb-4 relative"
                    x-data='{
                        open: false,
                        selected: "{{ old('category_id', $listing->category_id) }}",
                        categories: @json($categories->map(fn($cat) => ["id" => (string) $cat->id, "name" => $cat->pavadinimas])->values())
                    }'
                >
                    <label class="font-semibold text-black">Kategorija</label>
                
                    <input type="hidden" name="category_id" :value="selected" required>
                
                    <button
                        type="button"
                        @click="open = !open"
                        :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                        class="w-full rounded border py-2 px-3 text-left focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                        style="background-color: rgb(234, 220, 200)"
                    >
                        <span
                            class="text-black"
                            x-text="(categories.find(cat => cat.id === String(selected)) || {}).name || 'Pasirinkite kategoriją'"
                        ></span>
                
                        <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50 max-h-60 overflow-y-auto"
                        style="background-color: rgb(234, 220, 200); border-color: #836354"
                    >
                        @foreach($categories as $cat)
                            <div
                                @click="selected = '{{ $cat->id }}'; open = false"
                                class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                            >
                                {{ $cat->pavadinimas }}
                            </div>
                        @endforeach
                    </div>
                </div>

            <div x-show="type === 'preke'" x-transition>
                {{-- SIZE --}}
                <div class="mb-4 relative" x-data="{ open: false, selected: '{{ old('package_size', $listing->package_size) }}' }">
                    <label class="font-semibold text-black">Pakuotės dydis</label>

                    <input
                        type="hidden"
                        name="package_size"
                        :value="selected"
                        x-bind:required="type === 'preke'"
                        x-bind:disabled="type !== 'preke'"
                    >

                    <button
                        type="button"
                        @click="if (type === 'preke') open = !open"
                        :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                        class="w-full rounded border py-2 px-3 text-left focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                        style="background-color: rgb(234, 220, 200)"
                    >
                        <span
                            class="text-black"
                            x-text="selected === '' ? 'Pasirinkite dydį' : selected + (selected === 'XS' ? ' – Vokas' : selected === 'S' ? ' – Maža dėžė' : selected === 'M' ? ' – Vidutinė dėžė' : ' – Didelė dėžė')"
                        ></span>

                        <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        @click.outside="open = false"
                        class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50"
                        style="background-color: rgb(234, 220, 200); border-color: #836354"
                    >
                        <div @click="selected = 'XS'; open = false" class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">XS – Vokas</div>
                        <div @click="selected = 'S'; open = false" class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">S – Maža dėžė</div>
                        <div @click="selected = 'M'; open = false" class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">M – Vidutinė dėžė</div>
                        <div @click="selected = 'L'; open = false" class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]">L – Didelė dėžė</div>
                    </div>
                </div>

                {{-- QUANTITY --}}
                <div class="mb-4">
                    <label class="font-semibold text-black">Galimas kiekis</label>
                    <input 
                        type="number" 
                        min="1"
                        name="kiekis"
                        value="{{ old('kiekis', $listing->kiekis) }}"
                        class="w-full border border-gray-500 rounded px-3 py-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                        style="background-color: rgb(234, 220, 200)"
                        x-bind:required="type === 'preke'"
                        x-bind:disabled="type !== 'preke'">
                </div>

                {{-- RENEWABLE --}}
                <div class="mb-4 flex items-center gap-2">
                    <input 
                        type="checkbox" 
                        name="is_renewable"
                        value="1"
                        @checked($listing->is_renewable == 1)
                        class="rounded border-gray-500 text-[#836354] focus:ring-1 focus:ring-[#836354] focus:ring-offset-0"
                        style="background-color: rgb(234, 220, 200)"
                    >
                    <label class="text-black">Ar ši prekė atnaujinama (galima papildyti)?</label>
                </div>
            </div>

            {{-- NEW PHOTO UPLOAD + PREVIEW --}}
            <div class="mb-6" x-data="{ fileNames: '' }">
                <label class="block font-semibold mb-2">Pasirinkite naujas nuotraukas</label>
    
                <input 
                    type="file"
                    name="photos[]"
                    id="photoInput"
                    multiple
                    class="hidden"
                    @change="fileNames = Array.from($event.target.files).map(f => f.name).join(', ')"
                >
    
                <label
                    for="photoInput"
                    class="inline-flex items-center px-4 py-2 rounded cursor-pointer text-white hover:text-black transition-colors"
                    style="background-color: rgb(131, 99, 84)"
                >
                    Pasirinkti nuotraukas
                </label>
    
                <div
                    class="mt-3 p-3 rounded border text-sm text-black"
                    style="background-color: rgb(234, 220, 200); border-color: #836354"
                >
                    <span x-show="!fileNames">Nepasirinkta jokių failų</span>
                    <span x-show="fileNames" x-text="fileNames"></span>
                </div>
    
                <small class="text-gray-600 block mt-2">Galite pasirinkti keleta naujų nuotraukų.</small>
    
                <div
                    id="previewContainer"
                    class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4"
                ></div>
            </div>
            
            {{-- SAVE BUTTON --}}
            <button 
                class="text-white px-6 py-2 rounded hover:text-black"
                style="background-color: rgb(131, 99, 84)"
                type="submit">
                 Išsaugoti pakeitimus
            </button>

            <a 
                href="{{ route('my.listings') }}"
                class="text-white px-6 py-2 rounded hover:text-black"
                style="background-color: rgb(184, 80, 54)">
                Atšaukti
            </a>
        </form>

        {{-- EXISTING PHOTOS (OUTSIDE FORM) --}}
        <div class="mt-10">
            <label class="font-semibold text-lg text-black">Esamos nuotraukos</label>

            @if($listing->photos->isEmpty())
                <p class="text-black mt-2">Nuotraukų dar nėra.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                    @foreach($listing->photos as $photo)
                        <div class="relative border rounded overflow-hidden" style="border-color: #836354">

                            <img 
                                src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($photo->failo_url) }}" 
                                class="w-full h-48 object-cover">

                            {{-- DELETE BUTTON --}}
                            <form 
                                action="{{ route('listing.photo.delete', [$listing->id, $photo->id]) }}" 
                                method="POST"
                                class="absolute top-2 right-2">
                                @csrf
                                @method('DELETE')

                                <button 
                                    type="submit"
                                    @disabled($listing->photos->count() <= 1)
                                    class="text-white text-sm px-3 py-1 rounded shadow hover:text-black disabled:bg-gray-400 disabled:cursor-not-allowed"
                                    style="background-color: rgb(184, 80, 54);">
                                    Ištrinti
                                </button>

                            </form>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
    {{-- JS PREVIEW --}}
    <script>
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const preview = document.getElementById('previewContainer');
            preview.innerHTML = "";

            Array.from(e.target.files).forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function(event) {
                    const div = document.createElement('div');
                    div.classList.add("relative", "border", "rounded", "overflow-hidden");

                    div.innerHTML = `
                        <img src="${event.target.result}" class="w-full h-32 object-cover">
                        <button 
                            type="button" 
                            class="absolute top-2 right-2 text-white text-sm px-2 py-1 rounded hover:text-black"
                            style="background-color: rgb(184, 80, 54)"
                            onclick="removeSelectedFile(${index})">
                            X
                        </button>
                    `;

                    preview.appendChild(div);
                };

                reader.readAsDataURL(file);
            });
        });

        function removeSelectedFile(index) {
            let input = document.getElementById('photoInput');
            let files = Array.from(input.files);

            files.splice(index, 1);

            let dt = new DataTransfer();
            files.forEach(file => dt.items.add(file));

            input.files = dt.files;

            input.dispatchEvent(new Event('change'));
        }
    </script>

</x-app-layout>
