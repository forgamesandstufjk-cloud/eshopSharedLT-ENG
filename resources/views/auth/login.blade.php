<x-guest-layout>
    <x-auth-session-status class="mb-4 text-black" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-black">
                Prisijungti
            </h1>
        </div>

        <div>
            <label for="el_pastas" class="block font-medium text-sm text-black">
                {{ __('El. paštas') }}
            </label>
            <input
                id="el_pastas"
                class="block mt-1 w-full rounded-md border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                type="text"
                name="el_pastas"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('el_pastas')" class="mt-2" />
        </div>

        <div class="mt-4">
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

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-gray-500 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                    style="background-color: rgb(234, 220, 200)"
                    name="remember"
                >
                <span class="ms-2 text-sm text-black">
                    {{ __('Prisiminti mane') }}
                </span>
            </label>
        </div>

        <div class="mt-4 text-right">
            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="underline text-sm text-black hover:text-[#836354]"
                >
                    {{ __('Pamiršote slaptažodį?') }}
                </a>
            @endif
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4">
    <x-primary-button
        class="w-full justify-center text-white hover:text-black"
        style="background-color: rgb(131, 99, 84)"
    >
        {{ __('Prisijungti') }}
    </x-primary-button>

    <a
        href="{{ route('register') }}"
        class="inline-flex w-full items-center justify-center px-4 py-2 rounded-md border text-black hover:text-[#836354]"
        style="background-color: rgb(234, 220, 200)"
    >
        {{ __('Sukurti paskyrą') }}
    </a>
</div>
    </form>
</x-guest-layout>
