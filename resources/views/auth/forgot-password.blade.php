<x-guest-layout>
    <x-slot name="title">Pamiršai slaptažodį ?</x-slot>
    <div class="mb-4 text-sm text-black">
        {{ __('Pamiršote slaptažodį? Jokių problemų. Įveskite savo el. pašto adresą ir mes atsiųsime slaptažodžio atkūrimo nuorodą, kuri leis pasirinkti naują slaptažodį.') }}
    </div>

    <x-auth-session-status class="mb-4 text-black" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
           <label for="el_pastas" class="block font-medium text-sm text-black">
    {{ __('El. paštas') }}
</label>
<input
    id="el_pastas"
    class="block mt-1 w-full rounded-md border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#684F43] focus:border-[#684F43]"
    style="background-color: rgb(234, 220, 200)"
    type="email"
    name="el_pastas"
    value="{{ old('el_pastas') }}"
    required
    autofocus
/>
<x-input-error :messages="$errors->get('el_pastas')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <button
                type="submit"
                class="text-white px-6 py-3 rounded hover:text-black"
                style="background-color: rgb(104, 79, 67)"
            >
                {{ __('Siųsti slaptažodžio atkūrimo nuorodą') }}
            </button>
        </div>
    </form>
</x-guest-layout>
