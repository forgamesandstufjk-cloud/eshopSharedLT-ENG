<section class="space-y-6 text-black" style="background-color: rgb(215, 183, 142)">
    <header>
        <h2 class="text-lg font-medium text-black">
            {{ __('Ištrinti paskyrą') }}
        </h2>

        <p class="mt-1 text-sm text-black">
            {{ __('Ištrynus paskyrą, visi jos duomenys ir ištekliai bus negrįžtamai pašalinti.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="text-white hover:text-black"
        style="background-color: rgb(184, 80, 54)"
    >
        {{ __('Ištrinti paskyrą') }}
    </x-danger-button>

    <x-modal
        name="confirm-user-deletion"
        :show="$errors->userDeletion->isNotEmpty()"
        focusable
    >
        <form method="post"
              action="{{ route('profile.destroy') }}"
              class="p-6 text-black"
              style="background-color: rgb(215, 183, 142)">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-black">
                {{ __('Ar tikrai norite ištrinti savo paskyrą?') }}
            </h2>

            <p class="mt-1 text-sm text-black">
                {{ __('Ištrynus paskyrą, visi jos duomenys ir ištekliai bus negrįžtamai pašalinti. Įveskite savo slaptažodį, kad patvirtintumėte, jog norite visam laikui ištrinti paskyrą.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Slaptažodis') }}" class="sr-only" />

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 rounded-md text-black border border-gray-500 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                    style="background-color: rgb(234, 220, 200)"
                    placeholder="{{ __('Slaptažodis') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
               <button
                type="button"
                x-on:click="$dispatch('close')"
                class="px-4 py-2 text-white rounded hover:text-black transition"
                style="background-color: rgb(131, 99, 84)"
                >
                {{ __('Atšaukti') }}
                </button>
                <x-danger-button class="ms-3 text-white hover:text-black" style="background-color: rgb(184, 80, 54)">
                    {{ __('Ištrinti paskyrą') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
