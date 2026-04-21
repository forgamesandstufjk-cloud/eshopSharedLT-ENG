<x-guest-layout>
    <x-slot name="title">Registracija</x-slot>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-6">
        <h1 class="text-2xl font-bold text-black">
            Registracija
        </h1>
        </div>
        <!-- First Name -->
        <div>
            <label for="vardas" class="block font-medium text-sm text-black">
                {{ __('Vardas') }}
            </label>
            <input
                id="vardas"
                class="block mt-1 w-full rounded-md border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                type="text"
                name="vardas"
                value="{{ old('vardas') }}"
                required
                autofocus
                autocomplete="given-name"
            />
            <x-input-error :messages="$errors->get('vardas')" class="mt-2" />
        </div>

        <!-- Last Name -->
        <div class="mt-4">
            <label for="pavarde" class="block font-medium text-sm text-black">
                {{ __('Pavardė') }}
            </label>
            <input
                id="pavarde"
                class="block mt-1 w-full rounded-md border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                type="text"
                name="pavarde"
                value="{{ old('pavarde') }}"
                required
                autocomplete="family-name"
            />
            <x-input-error :messages="$errors->get('pavarde')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="el_pastas" class="block font-medium text-sm text-black">
                {{ __('El. paštas') }}
            </label>
            <input
                id="el_pastas"
                class="block mt-1 w-full rounded-md border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                type="email"
                name="el_pastas"
                value="{{ old('el_pastas') }}"
                required
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('el_pastas')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="slaptazodis" class="block font-medium text-sm text-black">
                {{ __('Slaptažodis') }}
            </label>
            <input
                id="slaptazodis"
                class="block mt-1 w-full rounded-md border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                type="password"
                name="slaptazodis"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('slaptazodis')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="slaptazodis_confirmation" class="block font-medium text-sm text-black">
                {{ __('Pakartokite slaptažodį') }}
            </label>
            <input
                id="slaptazodis_confirmation"
                class="block mt-1 w-full rounded-md border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                type="password"
                name="slaptazodis_confirmation"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('slaptazodis_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a
                href="{{ route('login') }}"
                class="underline text-sm text-black hover:text-[#836354]"
            >
                {{ __('Jau turite paskyrą?') }}
            </a>

            <button
                type="submit"
                class="text-white px-6 py-3 rounded hover:text-black"
                style="background-color: rgb(131, 99, 84)"
            >
                {{ __('Registruotis') }}
            </button>
        </div>
    </form>
</x-guest-layout>
