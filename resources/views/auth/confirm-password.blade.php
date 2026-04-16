<x-guest-layout>
    <div class="mb-4 text-sm text-black">
        {{ __('Tai saugi programos skiltis. Prieš tęsdami, patvirtinkite savo slaptažodį.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div>
            <label for="password" class="block font-medium text-sm text-black">
                {{ __('Slaptažodis') }}
            </label>

            <input
                id="password"
                class="block mt-1 w-full rounded-md border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <button
                type="submit"
                class="text-white px-6 py-3 rounded hover:text-black"
                style="background-color: rgb(131, 99, 84)"
            >
                {{ __('Patvirtinti') }}
            </button>
        </div>
    </form>
</x-guest-layout>
