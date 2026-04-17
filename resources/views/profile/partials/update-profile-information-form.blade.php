<style>
    label {
        color: #000000;
    }
</style>

<section>
    <header>
        <h2 class="text-lg font-medium text-black">
            {{ __('Profilio informacija') }}
        </h2>

        <p class="mt-1 text-sm text-black">
            {{ __("Atnaujinkite savo paskyros profilio informaciją.") }}
        </p>
    </header>

     {{-- BUYER CODE --}}
<div x-data="{ copied: false }" class="mt-5">
    <label for="buyer_code" class="block font-medium text-sm text-black">Jūsų pirkėjo kodas</label>

    <div class="mt-1 flex gap-2">
        <input
            id="buyer_code"
            type="text"
            readonly
            value="{{ $user->buyer_code ?? '—' }}"
            class="block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
            style="background-color: rgb(234, 220, 200)"
        />

        @if($user->buyer_code)
            <button
                type="button"
                @click="
                    navigator.clipboard.writeText('{{ $user->buyer_code }}');
                    copied = true;
                    setTimeout(() => copied = false, 2000);
                "
                class="px-4 py-2 rounded-md font-semibold text-white hover:text-black transition whitespace-nowrap"
                style="background-color: rgb(131, 99, 84)"
                x-text="copied ? 'Nukopijuota' : 'Kopijuoti'"
            >
                Kopijuoti
            </button>
        @endif
    </div>

    <p class="mt-1 text-xs text-black">
       {{ __("Šį kodą galite pateikti pardavėjui, kai jis kuria paslaugos užsakymą per svetainę.") }}
    </p>
</div>

    @if(session('error'))
    <div class="mt-4 mb-4 px-4 py-3 rounded text-black"
         style="background-color: rgb(207, 174, 134); border: 1px solid #836354">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="mt-4 mb-4 px-4 py-3 rounded text-black"
         style="background-color: rgb(207, 174, 134); border: 1px solid #836354">
        {{ session('success') }}
    </div>
@endif

    @if(session('missing_seller_requirements'))
    <div class="mb-6 px-4 py-3 rounded text-black"
         style="background-color: rgb(234, 220, 200); border: 1px solid #836354">
        <div class="font-semibold mb-2">Norėdami įkelti skelbimą, dar turite:</div>

        <ul class="list-disc pl-5 space-y-1">
            @foreach(session('missing_seller_requirements') as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </div>
@endif
    

    @php
        $currentCity      = $user->address?->City;
        $currentCountryId = $currentCity?->country_id;
        $currentCityId    = $currentCity?->id;
        $hasListings      = $user->listings()->count() > 0;
    @endphp

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- NAME --}}
        <div>
            <label for="vardas" class="block font-medium text-sm text-black">Vardas</label>
            <input
                id="vardas"
                name="vardas"
                type="text"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                value="{{ old('vardas', $user->vardas) }}"
                autocomplete="given-name"
            />
            <x-input-error class="mt-2" :messages="$errors->get('vardas')" />
        </div>

        {{-- LAST NAME --}}
        <div>
            <label for="pavarde" class="block font-medium text-sm text-black">Pavardė</label>
            <input
                id="pavarde"
                name="pavarde"
                type="text"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                value="{{ old('pavarde', $user->pavarde) }}"
                autocomplete="family-name"
            />
            <x-input-error class="mt-2" :messages="$errors->get('pavarde')" />
        </div>

        {{-- EMAIL --}}
        <div>
            <label for="el_pastas" class="block font-medium text-sm text-black">{{ __('El. paštas') }}</label>
            <input
                id="el_pastas"
                name="el_pastas"
                type="email"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                value="{{ old('el_pastas', $user->el_pastas) }}"
                autocomplete="email"
            />
            <x-input-error class="mt-2" :messages="$errors->get('el_pastas')" />
        </div>

        {{-- SELLER TOGGLE --}}
        <div x-data="{ isSeller: {{ $user->role === 'seller' ? 'true' : 'false' }} }" class="space-y-4">

            {{-- SELLER CHECKBOX --}}
 <div
    x-data="{
        isSeller: {{ old('role', $user->role === 'seller' ? 'seller' : '') === 'seller' ? 'true' : 'false' }}
    }"
>
    {{-- SELLER CHECKBOX --}}
    @if (!$hasListings)
        <label class="inline-flex items-center text-black cursor-pointer">
            <input
                type="checkbox"
                name="role"
                value="seller"
                class="h-5 w-5 rounded border-[#836354] text-[#836354] focus:ring-0 focus:outline-none"
                style="background-color: rgb(234, 220, 200)"
                :checked="isSeller"
                @change="isSeller = $event.target.checked"
            >
            <span class="ml-2">Esu pardavėjas / verslas</span>
        </label>
    @else
        <div class="text-sm text-black">
            Negalite išjungti pardavėjo režimo, nes turite aktyvių skelbimų arba pardavimų istoriją.
        </div>
    @endif

    <template x-if="isSeller">
        <div class="mt-4 space-y-4">
            @if(session('missing_seller_requirements'))
                <div class="p-3 rounded text-sm text-black"
                     style="background-color: rgb(207, 174, 134) border: 1px solid #836354">
                    Norint tapti pardavėju reikia bent vieno viešo kontakto ir šalies bei miesto.
                </div>
            @endif

                    <div class="text-sm text-black">
                        Ši informacija bus matoma Jūsų skelbimuose.
                    </div>

                    {{-- BUSINESS EMAIL --}}
                    <div>
                        <label for="business_email" class="block font-medium text-sm text-black">Verslo el. paštas (viešas)</label>
                        <input
                            id="business_email"
                            name="business_email"
                            type="email"
                            class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                            style="background-color: rgb(234, 220, 200)"
                            value="{{ old('business_email', $user->business_email) }}"
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('business_email')" />
                    </div>

                    {{-- PHONE --}}
                    <div>
                        <label for="telefonas" class="block font-medium text-sm text-black">Telefono numeris (viešas)</label>
                        <input
                            id="telefonas"
                            name="telefonas"
                            type="text"
                            inputmode="numeric"
                            pattern="^\+?[0-9]*$"
                            class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                            style="background-color: rgb(234, 220, 200)"
                            placeholder="+370xxxxxxx"
                            value="{{ old('telefonas', $user->telefonas) }}"
                            oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('telefonas')" />
                    </div>

                    <p class="text-xs text-black">
                        Pateikite bent vieną viešą kontaktinį būdą (el. paštą arba telefoną).
                    </p>

                </div>
            </template>
        </div>

       {{-- COUNTRY + CITY --}}
<div class="space-y-4 mt-6">
    <label class="block font-medium text-sm text-black">Vieta (privaloma pardavėjams)</label>

    <div
        x-data='{
            countries: @json(\App\Models\Country::select("id","pavadinimas")->orderBy("pavadinimas")->get()),
            cities: @json(\App\Models\City::select("id","pavadinimas","country_id")->orderBy("pavadinimas")->get()),
            countryId: "{{ $currentCountryId ?? '' }}",
            cityId: "{{ $currentCityId ?? '' }}",
            countryOpen: false,
            cityOpen: false,

            get selectedCountryName() {
                const country = this.countries.find(c => String(c.id) === String(this.countryId));
                return country ? country.pavadinimas : "Pasirinkite šalį";
            },

            get filteredCities() {
                if (!this.countryId) return [];
                return this.cities.filter(c => Number(c.country_id) === Number(this.countryId));
            },

            get selectedCityName() {
                const city = this.filteredCities.find(c => String(c.id) === String(this.cityId));
                return city ? city.pavadinimas : "Pasirinkite miestą";
            },

            setCountry(id) {
                if (String(this.countryId) !== String(id)) {
                    this.cityId = "";
                }
                this.countryId = String(id);
                this.countryOpen = false;
            },

            setCity(id) {
                this.cityId = String(id);
                this.cityOpen = false;
            }
        }'
        class="space-y-4"
    >

        {{-- COUNTRY --}}
        <div class="relative">
            <label for="country_id" class="block font-medium text-sm text-black">Šalis</label>
            <input type="hidden" id="country_id" name="country_id" x-model="countryId">

            <button
                type="button"
                @click="countryOpen = !countryOpen; cityOpen = false"
                :class="countryOpen ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                class="mt-1 block w-full rounded-md border py-2 px-3 text-left text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                style="background-color: rgb(234, 220, 200)"
            >
                <span x-text="selectedCountryName"></span>
                <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                x-show="countryOpen"
                @click.outside="countryOpen = false"
                class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50 max-h-60 overflow-y-auto"
                style="background-color: rgb(234, 220, 200); border-color: #836354"
            >
                <div
                    @click="setCountry('')"
                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                >
                    Pasirinkite šalį
                </div>

                <template x-for="country in countries" :key="country.id">
                    <div
                        @click="setCountry(country.id)"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                        x-text="country.pavadinimas"
                    ></div>
                </template>
            </div>
        </div>

        {{-- CITY --}}
        <div class="relative">
            <label for="city_id" class="block font-medium text-sm text-black">Miestas</label>
            <input type="hidden" id="city_id" name="city_id" x-model="cityId">

            <button
                type="button"
                @click="if (countryId) { cityOpen = !cityOpen; countryOpen = false }"
                :class="cityOpen ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                class="mt-1 block w-full rounded-md border py-2 px-3 text-left text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                style="background-color: rgb(234, 220, 200)"
            >
                <span x-text="selectedCityName"></span>
                <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                x-show="cityOpen"
                @click.outside="cityOpen = false"
                class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50 max-h-60 overflow-y-auto"
                style="background-color: rgb(234, 220, 200); border-color: #836354"
            >
                <div
                    @click="setCity('')"
                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                >
                    Pasirinkite miestą
                </div>

                <template x-for="city in filteredCities" :key="city.id">
                    <div
                        @click="setCity(city.id)"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                        x-text="city.pavadinimas"
                    ></div>
                </template>
            </div>
        </div>
    </div>
</div>
        {{-- ADDRESS --}}
        <div class="space-y-4 mt-8">
            <label class="block font-medium text-sm text-black">Adresas (nebūtinas)</label>

            {{-- STREET --}}
            <div>
                <label for="gatve" class="block font-medium text-sm text-black">Gatvė</label>
                <input
                    id="gatve"
                    name="gatve"
                    placeholder="Gatvės pavadinimas"
                    class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                    style="background-color: rgb(234, 220, 200)"
                    value="{{ old('gatve', $user->address->gatve ?? '') }}"
                />
                <x-input-error class="mt-1" :messages="$errors->get('gatve')" />
            </div>

            {{-- HOUSE NUMBER --}}
            <div>
                <label for="namo_nr" class="block font-medium text-sm text-black">Namo numeris</label>
                <input
                    id="namo_nr"
                    name="namo_nr"
                    placeholder="pvz. 12A"
                    class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                    style="background-color: rgb(234, 220, 200)"
                    value="{{ old('namo_nr', $user->address->namo_nr ?? '') }}"
                />
                <x-input-error class="mt-1" :messages="$errors->get('namo_nr')" />
            </div>

            {{-- FLAT NUMBER --}}
            <div>
                <label for="buto_nr" class="block font-medium text-sm text-black">Buto durų numeris </label>
                <input
                    id="buto_nr"
                    name="buto_nr"
                    placeholder="e.g. 5"
                    class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                    style="background-color: rgb(234, 220, 200)"
                    value="{{ old('buto_nr', $user->address->buto_nr ?? '') }}"
                />
                <x-input-error class="mt-1" :messages="$errors->get('buto_nr')" />
            </div>
        </div>

        <div class="flex items-center gap-4 mt-4">
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 rounded-md font-semibold text-white hover:text-black transition"
                style="background-color: rgb(131, 99, 84)"
            >
                {{ __('Išsaugoti') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-black"
                >
                    {{ __('Išsaugota.') }}
                </p>
            @endif
        </div>
    </form>

    {{-- STRIPE CONNECT SECTION --}}
    @if ($user->role === 'seller')
        <div class="mt-8 p-4 border rounded-lg" style="background-color: rgb(234, 220, 200); border-color: #6B7280">

            @if (!$user->stripe_onboarded)
                <h3 class="text-md font-semibold text-black">
                    Stripe išmokėjimai neprijungti
                </h3>

                <p class="mt-1 text-sm text-black">
                   Norėdami gauti mokėjimus iš pirkėjų, turite prijungti savo Stripe paskyrą.
                    <br>(platformos mokestis – 10 %)
                </p>

                <a
                    href="{{ route('stripe.connect') }}"
                    class="inline-block mt-4 px-4 py-2 text-white rounded-md hover:text-black transition"
                    style="background-color: rgb(131, 99, 84)"
                >
                    Prijungti Stripe
                </a>
            @else
                <h3 class="text-md font-semibold text-black">
                    Stripe prijungta
                </h3>

                <p class="mt-1 text-sm text-black">
                   Dabar galite gauti mokėjimus ir talpinti skelbimus. (platformos mokestis – 10 %)
                </p>

                @if(auth()->user()->stripe_onboarded)
                    <a
                        href="{{ route('stripe.dashboard') }}"
                        target="_blank"
                        class="inline-block mt-3 px-4 py-2 text-white rounded-md hover:text-black transition"
                        style="background-color: rgb(131, 99, 84)"
                    >
                       Peržiūrėti Stripe pajamas
                    </a>
                @endif
            @endif

        </div>
    @endif
</section>
