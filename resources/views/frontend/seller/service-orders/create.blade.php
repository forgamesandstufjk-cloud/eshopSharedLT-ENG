<x-app-layout>
    <div class="max-w-4xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0 pb-10">
        <h1 class="text-xl sm:text-2xl font-bold mb-6 text-black">
            {{ $serviceOrder ? 'Redaguoti paslaugos užsakymą' : 'Sukurti paslaugos užsakymą' }}
        </h1>

        @if ($errors->any())
            <div class="mb-4 p-4 rounded border" style="background-color: rgb(207, 174, 134); border-color: #836354">
                <ul class="list-disc pl-5 text-black text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="shadow rounded p-5" style="background-color: rgb(215, 183, 142)">
            <form method="POST"
                  action="{{ $serviceOrder ? route('seller.service-orders.update', $serviceOrder) : route('seller.service-orders.store') }}"
                  class="space-y-4">
                @csrf
                @if($serviceOrder)
                    @method('PUT')
                @endif

                <div
                    class="relative"
                    x-data="{
                        open: false,
                        selected: '{{ old('listing_id', $serviceOrder?->listing_id ?? $listing?->id ?? '') }}'
                    }"
                >
                    <label class="block text-sm font-medium text-black mb-1">Skelbimas</label>

                    @if(!$serviceOrder)
                        <input type="hidden" name="listing_id" :value="selected">

                        <button
                            type="button"
                            @click="open = !open"
                            :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                            class="w-full rounded border py-2 px-3 text-left text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                            style="background-color: rgb(234, 220, 200);"
                        >
                            <span x-text="selected === '' ? 'Pasirinkite skelbimą' : (() => {
                                const listings = {
                                    @foreach($listings as $item)
                                        '{{ $item->id }}': '{{ $item->pavadinimas }} (€{{ number_format($item->kaina, 2) }})',
                                    @endforeach
                                };
                                return listings[selected] ?? 'Pasirinkite skelbimą';
                            })()"></span>

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
                            @foreach($listings as $item)
                                <div
                                    @click="selected = '{{ $item->id }}'; open = false"
                                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                                >
                                    {{ $item->pavadinimas }} (€{{ number_format($item->kaina, 2) }})
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="w-full rounded border py-2 px-3 text-black"
                            style="background-color: rgb(234, 220, 200); border-color: #836354;"
                        >
                            @php
                                $selectedListing = $listings->firstWhere('id', $serviceOrder->listing_id);
                            @endphp
                            {{ $selectedListing?->pavadinimas }} (€{{ number_format($selectedListing?->kaina ?? 0, 2) }})
                        </div>
                        <input type="hidden" name="listing_id" value="{{ $serviceOrder->listing_id }}">
                    @endif

                    <p class="text-xs text-black mt-1">
                        Pasirinkite, kuriam paslaugos skelbimui kuriamas užsakymas.
                    </p>
                </div>

                <div x-data="{ anonymous: {{ old('is_anonymous', $serviceOrder?->is_anonymous) ? 'true' : 'false' }} }">
                    <div class="flex items-center gap-2">
                        <input type="checkbox"
                            id="is_anonymous"
                            name="is_anonymous"
                            value="1"
                            x-model="anonymous"
                            @checked(old('is_anonymous', $serviceOrder?->is_anonymous))
                            class="h-4 w-4 rounded border appearance-none checked:bg-[#836354] checked:border-[#836354] focus:outline-none focus:ring-1 focus:ring-[#836354]"
                            style="border-color: #836354; background-color: rgb(234, 220, 200);">
                        <label for="is_anonymous" class="text-black">Pirkėjas nenurodytas</label>
                    </div>

                    <p class="text-xs text-black mt-1">
                        Pažymėkite, jei užsakymas nėra susietas su registruotu pirkėju.
                    </p>

                    <div x-show="!anonymous" class="mt-4">
                        <label class="block text-sm font-medium text-black mb-1">Pirkėjo kodas</label>
                        <input type="text"
                               name="buyer_code"
                               value="{{ old('buyer_code', $serviceOrder?->buyer_code_snapshot) }}"
                               class="w-full border rounded p-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                               style="background-color: rgb(234, 220, 200); border-color: #836354;"
                               placeholder="Pvz. F5FRG3">
                        <p class="text-xs text-black mt-1">
                            Palikite tuščią, jei pirkėjas nepriskiriamas.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-black mb-1">Galutinė sutarta kaina</label>
                    <input type="number"
                           step="0.01"
                           min="0.01"
                           name="final_price"
                           value="{{ old('final_price', $serviceOrder?->final_price ?? $listing?->kaina ?? '') }}"
                           class="w-full border rounded p-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                           style="background-color: rgb(234, 220, 200); border-color: #836354;"
                           placeholder="Pvz. 49.99"
                           onwheel="event.preventDefault()"
                           required>
                    <p class="text-xs text-black mt-1">
                        Įveskite galutinę su pirkėju sutartą sumą eurais.
                    </p>
                </div>

                <div class="relative"
                     x-data="{ open: false, selected: '{{ old('package_size', $serviceOrder?->package_size ?? 'S') }}' }">
                    <label class="block text-sm font-medium text-black mb-1">Siuntos dydis</label>

                    <input type="hidden" name="package_size" :value="selected">

                    <button
                        type="button"
                        @click="open = !open"
                        :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                        class="w-full rounded border py-2 px-3 text-left text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                        style="background-color: rgb(234, 220, 200);"
                    >
                        <span x-text="
                        selected === 'S' ? 'S – 64×38×9 cm, iki 25 kg' :
                        selected === 'M' ? 'M – 64×38×19 cm, iki 25 kg' :
                        'L – 64×38×39 cm, iki 25 kg'
                    "></span>

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

                    <p class="text-xs text-black mt-1">
                        Šis dydis bus naudojamas apskaičiuoti pristatymo kainą, kai pirkėjas apmokės per svetainę.
                    </p>
                </div>
                <label class="block text-sm font-medium text-black mb-3">Papildomi laukeliai</label>
                <div>
                    <label class="block text-sm font-medium text-black mb-1">Sutartos specifikacijos</label>
                    <textarea name="agreed_specifications" rows="4"
                              class="w-full border rounded p-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                              style="background-color: rgb(234, 220, 200); border-color: #836354"
                              placeholder="Aprašykite, kas tiksliai sutarta: apimtis, medžiagos, variantai, formatas ir pan.">{{ old('agreed_specifications', data_get($serviceOrder?->agreed_details, 'agreed_specifications')) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-black mb-1">Pastabos</label>
                    <textarea name="notes" rows="3"
                              class="w-full border rounded p-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                              style="background-color: rgb(234, 220, 200); border-color: #836354"
                              placeholder="Bendros pastabos apie užsakymą, susitarimus ar papildomą informaciją.">{{ old('notes', $serviceOrder?->notes) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-black mb-1">Siuntimo pastabos</label>
                    <textarea name="shipping_notes" rows="3"
                              class="w-full border rounded p-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                              style="background-color: rgb(234, 220, 200); border-color: #836354"
                              placeholder="Jei aktualu, nurodykite pristatymo būdą, terminą, adresą ar kitą siuntimo informaciją.">{{ old('shipping_notes', $serviceOrder?->shipping_notes) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-black mb-1">Papildomi reikalavimai</label>
                    <textarea name="custom_requirements" rows="3"
                              class="w-full border rounded p-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                              style="background-color: rgb(234, 220, 200); border-color: #836354"
                              placeholder="Įrašykite papildomus pirkėjo pageidavimus ar techninius reikalavimus.">{{ old('custom_requirements', $serviceOrder?->custom_requirements) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-black mb-1">Termino / eigos pastabos</label>
                    <textarea name="timeline_notes" rows="3"
                              class="w-full border rounded p-2 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                              style="background-color: rgb(234, 220, 200); border-color: #836354"
                              placeholder="Nurodykite darbų eigą, etapus, terminus ar svarbias datas.">{{ old('timeline_notes', $serviceOrder?->timeline_notes) }}</textarea>
                </div>

                <div class="pt-2 flex gap-2">
                    <button class="text-white px-4 py-2 rounded hover:text-black" style="background-color: rgb(131, 99, 84)">
                        {{ $serviceOrder ? 'Išsaugoti pakeitimus' : 'Sukurti užsakymą' }}
                    </button>

                    <a href="{{ route('seller.service-orders.index') }}"
                       class="px-4 py-2 rounded text-white hover:text-black"
                       style="background-color: rgb(184, 80, 54)">
                        Atšaukti
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
