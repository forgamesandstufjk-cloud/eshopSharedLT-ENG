<section>
    <header>
        <h2 class="text-lg font-medium text-black">
            {{ __('Atnaujinti slaptažodį') }}
        </h2>

        <p class="mt-1 text-sm text-black">
            {{ __('Įsitikinkite, kad jūsų paskyra naudoja ilgą ir atsitiktinį slaptažodį, kad išliktų saugi.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block font-medium text-sm text-black">
                {{ __('Dabartinis slaptažodi') }}
            </label>
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200);"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block font-medium text-sm text-black">
                {{ __('Naujas slaptažodis') }}
            </label>
            <input
                id="update_password_password"
                name="password"
                type="password"
                autocomplete="new-password"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200);"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block font-medium text-sm text-black">
                {{ __('Patvirtinti slaptažodį') }}
            </label>
            <input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-500 text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200);"
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="text-white hover:text-black" style="background-color: rgb(131, 99, 84);">
                {{ __('Išsaugoti') }}
            </x-primary-button>

            @if (session('status') === 'password-updated')
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
</section>
