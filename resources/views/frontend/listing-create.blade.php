<x-app-layout> 
      <x-slot name="title">Skelbimo kūrimas</x-slot>
<div class="min-h-screen w-full py-10" style="background-color: rgb(234, 220, 200)">
<div class="max-w-3xl mx-auto shadow p-6 rounded mt-10 mb-10"
     style="background-color: rgb(215, 183, 142)"
     x-data="{ type: '{{ old('tipas', 'preke') }}' }">

    <h1 class="text-3xl font-bold mb-6">Sukurti naują skelbimą</h1>

    {{-- ERROR DISPLAY --}}
    @if ($errors->any())
    <div class="p-4 rounded mb-6 border"
         style="background-color: rgb(234, 220, 200); border-color: rgb(184, 80, 54); color: rgb(184, 80, 54)">
        <div class="font-semibold mb-2">Prašome pataisyti laukus:</div>

        <div class="space-y-1 text-sm">
            @foreach ($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

    <form action="{{ route('listing.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- TYPE --}}
        <div class="mb-4 relative" x-data="{ open: false }">
            <label class="block font-semibold">Skelbimo tipas</label>

            <input type="hidden" name="tipas" x-model="type" required>

            <button
                type="button"
                @click="open = !open"
                :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                class="w-full rounded border py-2 px-3 text-left focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                style="background-color: rgb(234, 220, 200)"
            >
                <span class="text-black" x-text="type === '' ? 'Pasirinkite tipą' : (type === 'preke' ? 'Prekė' : 'Paslauga')"></span>

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

        {{-- TITLE --}}
        <div class="mb-4">
            <label class="block font-semibold">Pavadinimas</label>
               <input
                    type="text"
                    name="pavadinimas"
                    value="{{ old('pavadinimas') }}"
                    class="w-full border rounded p-2 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                    style="background-color: rgb(234, 220, 200)"
                    maxlength="255"
                    required
               >
             
        </div>

        {{-- DESCRIPTION --}}
        <div class="mb-4">
            <label class="block font-semibold">Aprašymas</label>
            <textarea
    name="aprasymas"
    rows="5"
    class="w-full border rounded p-2 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
    style="background-color: rgb(234, 220, 200)"
      maxlength="2000"
    required
>{{ old('aprasymas') }}</textarea>
        </div>

        {{-- CATEGORY --}}
        <div class="mb-4 relative" x-data="{ open: false, selected: '{{ old('category_id', '') }}' }">
            <label class="block font-semibold">Kategorija</label>

            <input type="hidden" name="category_id" x-bind:value="selected" required>

            <button
                type="button"
                @click="open = !open"
                :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                class="w-full rounded border py-2 px-3 text-left focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                style="background-color: rgb(234, 220, 200)"
            >
                <span
                    class="text-black"
                    x-text="selected === '' ? 'Pasirinkite kategoriją' : (() => {
                        const categories = {
                            @foreach(\App\Models\Category::all() as $cat)
                                '{{ $cat->id }}': '{{ $cat->pavadinimas }}',
                            @endforeach
                        };
                        return categories[selected] ?? 'Pasirinkite kategoriją';
                    })()"
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
                @foreach(\App\Models\Category::all() as $cat)
                    <div
                        @click="selected = '{{ $cat->id }}'; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        {{ $cat->pavadinimas }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- PRODUCT ONLY FIELDS --}}
        <div x-show="type === 'preke'" x-transition>
            <div class="mb-4 relative" x-data="{ open: false, selected: '{{ old('package_size', '') }}' }">
                <label class="font-semibold">Pakuotės dydis</label>

                <input
                    type="hidden"
                    name="package_size"
                    x-bind:value="selected"
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
                    <div
                        @click="selected = 'XS'; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        XS – 61x18x8 cm, iki 1 kg
                    </div>

                    <div
                        @click="selected = 'S'; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        S – 64×38×9 cm, iki 25 kg
                    </div>

                    <div
                        @click="selected = 'M'; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        M – 64×38×19 cm, iki 25 kg
                    </div>

                    <div
                        @click="selected = 'L'; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        L – 64×38×39 cm, iki 25 kg
                    </div>
                </div>
            </div>
             
            {{-- QUANTITY --}}
            <div class="mb-4">
                <label class="font-semibold">Galimas kiekis</label>
                <input
    type="number"
    name="kiekis"
    value="{{ old('kiekis', 1) }}"
    min="1"
    max="999"
    class="w-full border p-2 rounded focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
    style="background-color: rgb(234, 220, 200)"
    required
>
            </div>

            {{-- RENEWABLE --}}
            <div class="mb-4 flex items-center gap-2">
                <input
                   type="checkbox"
                   name="is_renewable"
                   value="1"
                   {{ old('is_renewable') ? 'checked' : '' }}
                   class="appearance-none w-4 h-4 rounded border border-gray-500 checked:bg-[#836354] checked:border-[#836354] focus:outline-none focus:ring-1 focus:ring-[#836354] focus:ring-offset-0"
                   style="background-color: rgb(234, 220, 200)">
                 
                <label>Ar tai atnaujinamas produktas (galima papildyti atsargas)?</label>
            </div>
        </div>

       {{-- PRICE --}}
          <div class="mb-4">
              <label class="block font-semibold">Kaina (€)</label>
              <input
                  type="number"
                  min="0.20"
                  max="99999"
                  step="0.01"
                  name="kaina"
                   value="{{ old('kaina', '0.20') }}"
                  class="w-full border rounded p-2 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                  style="background-color: rgb(234, 220, 200)"
                  onwheel="event.preventDefault()"
                  required
              >
          </div>

               {{-- PHOTOS WITH LIVE PREVIEW --}}
               <div class="mb-6" x-data="{ fileNames: '' }">
                   <label class="block font-semibold mb-2">Nuotraukos</label>
               
                   <input 
                       type="file"
                       name="photos[]"
                       id="photoInput"
                       multiple
                       accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                       required
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
               
                   <small class="text-gray-600 block mt-2">
                       Galite įkelti tik nuotraukas: JPG, JPEG, PNG.
                   </small>
               
                   <div
                       id="previewContainer"
                       class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4"
                   ></div>
               </div>

        <div class="flex gap-4 mt-6">         
            <button
                type="submit"
                class="bg-[#B86B6B] hover:text-black text-white px-6 py-3 rounded"
                style="background-color: rgb(131, 99, 84)"
            >
                Paskelbti skelbimą
            </button>

            <a 
                href="{{ route('my.listings') }}"
                class="bg-[#B86B6B] hover:text-black text-white px-6 py-3 rounded"
                style="background-color: rgb(184, 80, 54)">
                Atšaukti
            </a>
        </div>
    </form>
</div>
</div>

{{-- LIVE PREVIEW --}}
<script>
const photoInput = document.getElementById('photoInput');

photoInput.addEventListener('change', function (e) {
    const preview = document.getElementById('previewContainer');
    preview.innerHTML = "";

    const allowedTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
    ];

    let files = Array.from(e.target.files);
    const validFiles = files.filter(file => allowedTypes.includes(file.type));

    if (validFiles.length !== files.length) {
        alert('Galite pasirinkti tik nuotraukas: JPG, JPEG, PNG.');

        const dataTransfer = new DataTransfer();
        validFiles.forEach(file => dataTransfer.items.add(file));
        photoInput.files = dataTransfer.files;

        photoInput.dispatchEvent(new Event('change'));
        return;
    }

    validFiles.forEach((file, index) => {
        const reader = new FileReader();

        reader.onload = function (event) {
            const wrapper = document.createElement('div');
            wrapper.classList.add("relative", "border", "rounded", "overflow-hidden");

            wrapper.innerHTML = `
                <img src="${event.target.result}" class="w-full h-32 object-cover">
                <button 
                    type="button" 
                    class="absolute top-2 right-2 text-white text-sm px-2 py-1 rounded hover:text-black"
                    style="background-color: rgb(184, 80, 54)"
                    onclick="removeSelectedFile(${index})">
                    X
                </button>
            `;

            preview.appendChild(wrapper);
        };

        reader.readAsDataURL(file);
    });
});

function removeSelectedFile(index) {
    let input = document.getElementById('photoInput');
    let files = Array.from(input.files);

    files.splice(index, 1);

    const dataTransfer = new DataTransfer();
    files.forEach(file => dataTransfer.items.add(file));

    input.files = dataTransfer.files;
    input.dispatchEvent(new Event('change'));
}
</script>
</x-app-layout>
