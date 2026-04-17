<nav x-data="{ open: false, filtersOpen: false }" class="shadow sticky top-0 z-50">
    @php
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    @endphp
    <!-- TOP BAR — Logo + Main Links -->
    <div style="background-color: rgb(215, 183, 142)">
        <div class="w-full px-4 sm:px-6 lg:px-8 min-h-16 flex items-center justify-between gap-3">

            <!-- LEFT -->
            <div class="flex items-center gap-4 lg:gap-8 min-w-0">
                <!-- LOGO -->
                <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.reported-listings.index'): route('home') }}"
                   class="text-xl sm:text-2xl font-bold text-black shrink-0 hover:text-white">
                    Keblu.lt
                </a>

                <!-- DESKTOP MAIN NAVIGATION -->
                <div class="hidden md:flex items-center space-x-4 lg:space-x-6 text-black font-medium">
                    <a href="{{ route('home', ['tipas' => 'preke']) }}" class="hover:text-white">
                        Prekės
                    </a>

                    <a href="{{ route('home', ['tipas' => 'paslauga']) }}" class="hover:text-white">
                        Paslaugos
                    </a>

                    @auth
                        @if($isAdmin)
                            <a href="{{ route('admin.shipments.index') }}" class="hover:text-white">
                                Siuntų peržiūra
                            </a>

                            <a href="{{ route('admin.reported-listings.index') }}" class="hover:text-white">
                                Pranešti skelbimai
                            </a>

                            <a href="{{ route('admin.reported-listings.reported-comments') }}" class="text-black hover:text-white">
                                Pranešti atsiliepimai
                            </a>
                        @else
                            <a href="{{ route('favorites.page') }}" class="hover:text-white">
                                Išsaugoti
                            </a>
                                
                            @if(auth()->user()->role === 'seller' && auth()->user()->canUseSellerFeatures())
                                <a href="{{ route('my.listings') }}" class="text-black hover:text-white">
                                    Mano skelbimai
                                </a>
                            @endif
           
                            @if(auth()->user()->canUseSellerFeatures())
                                <a href="{{ route('listing.create') }}" class="text-black hover:text-white">
                                    Įkelti skelbimą
                                </a>
                            @endif

                            <a href="{{ route('buyer.orders') }}" class="hover:text-white">
                                Mano pirkimai
                            </a>

                            @if(auth()->user()->role === 'seller')
                                <a href="{{ route('seller.orders') }}" class="hover:text-white">
                                    Mano pardavimai
                                </a>
                            @endif

                            @if(auth()->user()->role === 'seller')
                                <a href="{{ route('seller.service-orders.index') }}" class="hover:text-white">
                                    Paslaugų užsakymai
                                </a>
                            @endif
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="hover:text-white">
                            Išsaugoti
                        </a>

                        <a href="{{ route('login') }}" class="hover:text-white">
                            Mano skelbimai
                        </a>

                        <a href="{{ route('login') }}" class="hover:text-white">
                            Įkelti skelbimą
                        </a>
                    @endauth
                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                @auth
                    @unless($isAdmin)
                        <!-- CART LINK -->
<a href="{{ route('cart.index') }}" class="relative text-black hover:text-white inline-flex items-center" aria-label="Krepšelis">
    <svg xmlns="http://www.w3.org/2000/svg"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="1.8"
         class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437m0 0L6.75 11.25m-1.644-5.977h13.239c.917 0 1.593.862 1.375 1.752l-1.273 5.25a1.125 1.125 0 01-1.094.86H6.75m-1.644-7.837L6.75 11.25m0 0L5.94 14.49a1.125 1.125 0 001.09 1.385h10.72M9 19.5a.75.75 0 100 1.5.75.75 0 000-1.5zm9 0a.75.75 0 100 1.5.75.75 0 000-1.5z" />
    </svg>

    @if(session('cart_count', 0) > 0)
        <span class="absolute -top-2 -right-2 text-white text-xs rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1"
              style="background-color: rgb(131, 99, 84)">
            {{ session('cart_count') }}
        </span>
    @endif
</a>
                    @endunless

                    <!-- USER DROPDOWN DESKTOP -->
                    <div class="hidden md:block">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-black font-bold"
                                        style="background-color: rgb(215, 183, 142)">
                                    <span>{{ Auth::user()->vardas }}</span>
                                    <svg class="ms-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                              clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    Profilis
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                                     onclick="event.preventDefault(); this.closest('form').submit();">
                                        Atsijungti
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @else
                    <div class="hidden md:flex items-center gap-4">
                        <a href="{{ route('login') }}" class="text-black font-medium hover:text-white">Prisijungti</a>
                    </div>
                @endauth

                <!-- MOBILE MENU BUTTON -->
                <button
                    @click="open = !open"
                    class="md:hidden inline-flex items-center justify-center p-2 rounded text-black"
                    type="button"
                >
                    <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- MOBILE MENU -->
        <div x-show="open" class="md:hidden px-4 pb-4 space-y-3 text-black font-medium">
            <a href="{{ route('home', ['tipas' => 'preke']) }}" class="block hover:text-white">
                Prekės
            </a>

            <a href="{{ route('home', ['tipas' => 'paslauga']) }}" class="block hover:text-white">
                Paslaugos
            </a>

            @auth
                @if($isAdmin)
                    <a href="{{ route('admin.shipments.index') }}" class="block hover:text-white">
                        Siuntų peržiūra
                    </a>

                    <a href="{{ route('admin.reported-listings.index') }}" class="block hover:text-white">
                        Pranešti skelbimai
                    </a>

                    <a href="{{ route('admin.reported-listings.reported-comments') }}" class="block hover:text-white">
                        Pranešti atsiliepimai
                    </a>
                @else
                    <a href="{{ route('favorites.page') }}" class="block hover:text-white">
                        Išsaugoti
                    </a>

                    @if(auth()->user()->canUseSellerFeatures())
                        <a href="{{ route('my.listings') }}" class="block hover:text-white">
                            Mano skelbimai
                        </a>

                        <a href="{{ route('listing.create') }}" class="block hover:text-white">
                            Įkelti skelbimą
                        </a>
                    @endif

                    <a href="{{ route('buyer.orders') }}" class="block hover:text-white">
                        Mano pirkimai
                    </a>

                    @if(auth()->user()->role === 'seller')
                        <a href="{{ route('seller.orders') }}" class="block hover:text-white">
                            Mano pardavimai
                        </a>
                        <a href="{{ route('seller.service-orders.index') }}" class="block hover:text-white">
                            Paslaugų užsakymai
                        </a>
                    @endif

                    <a href="{{ route('profile.edit') }}" class="block hover:text-white">
                        Profilis
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block hover:text-white text-left w-full">
                            Atsijungti
                        </button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="block hover:text-white">
                    Išsaugoti
                </a>

                <a href="{{ route('login') }}" class="block hover:text-white">
                    Mano skelbimai
                </a>

                <a href="{{ route('login') }}" class="block hover:text-white">
                    Įkelti skelbimą
                </a>

                <a href="{{ route('login') }}" class="block hover:text-white">
                    Prisijungti
                </a>

                <a href="{{ route('register') }}" class="block hover:text-white">
                    Registruotis
                </a>
            @endauth
        </div>
    </div>

    @php
        $showSearchNav = request()->routeIs('home', 'search.listings');
    @endphp

    @if($showSearchNav)
    <!-- BOTTOM BAR — Search + Filters -->
    <div class="bg-repeat-x bg-top" style="background-image: url('{{ asset('images/nav.png') }}'); background-size: auto 100%">
        <div class="px-4 sm:px-6 lg:px-8 py-3">
            <div class="max-w-6xl mx-auto flex flex-col sm:flex-row gap-3 sm:items-center justify-center">

                <!-- SEARCH BAR -->
                <form action="{{ route('search.listings') }}" method="GET" class="grid grid-cols-3 sm:flex w-full sm:max-w-3xl mx-auto">
                    <input
                        type="text"
                        name="q"
                        class="col-span-2 sm:flex-grow border rounded-l px-4 py-2 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                        style="background-color: rgb(234, 220, 200, 0.8)"
                        placeholder="Ieškoti skelbimo..."
                        value="{{ request('q') }}"
                    >
                    <button class="text-white px-4 py-2 rounded-r hover:text-black"
                            style="background-color: rgb(131, 99, 84)">
                        Ieškoti
                    </button>
                </form>
                <!-- FILTERS + SORT -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:flex sm:gap-3 justify-center">
    <button
        type="button"
        @click.stop="filtersOpen = !filtersOpen"
        class="px-4 py-2 rounded hover:text-black text-white w-full sm:w-auto"
        style="background-color: rgb(131, 99, 84)"
    >
        Filtrai
    </button>

    <form method="GET" action="{{ url()->current() }}" class="w-full sm:w-auto">
        @foreach(request()->except('sort') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        <div class="relative" x-data="{ sortOpen: false, selectedSort: '{{ request('sort', '') }}' }" @keydown.escape.window="sortOpen = false">
            <input type="hidden" name="sort" :value="selectedSort">

            <button
                type="button"
                @click.stop="sortOpen = !sortOpen"
                :class="sortOpen ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-[#836354]'"
                class="px-3 py-2 rounded border text-black focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center w-full sm:w-auto sm:min-w-[220px]"
                style="background-color: rgba(234, 220, 200, 0.8)"
            >
                <span class="truncate" x-text="
                    selectedSort === '' ? 'Rūšiuoti' :
                    selectedSort === 'newest' ? 'Naujausi pirmiausia' :
                    selectedSort === 'oldest' ? 'Seniausi pirmiausia' :
                    selectedSort === 'price_asc' ? 'Kaina: nuo mažiausios' :
                    'Kaina: nuo didžiausios'
                "></span>

                <svg class="h-5 w-5 text-black ml-2 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                x-show="sortOpen"
                x-cloak
                x-transition
                @click.outside="sortOpen = false"
                class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50"
                style="background-color: rgb(234, 220, 200); border-color: #836354"
            >
                <div
                    @click="selectedSort = ''; sortOpen = false; $nextTick(() => $el.closest('form').submit())"
                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354]"
                >
                    Rūšiuoti
                </div>

                <div
                    @click="selectedSort = 'newest'; sortOpen = false; $nextTick(() => $el.closest('form').submit())"
                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354]"
                >
                    Naujausi pirmiausia
                </div>

                <div
                    @click="selectedSort = 'oldest'; sortOpen = false; $nextTick(() => $el.closest('form').submit())"
                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354]"
                >
                    Seniausi pirmiausia
                </div>

                <div
                    @click="selectedSort = 'price_asc'; sortOpen = false; $nextTick(() => $el.closest('form').submit())"
                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354]"
                >
                    Kaina: nuo mažiausios
                </div>

                <div
                    @click="selectedSort = 'price_desc'; sortOpen = false; $nextTick(() => $el.closest('form').submit())"
                    class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#836354]"
                >
                    Kaina: nuo didžiausios
                </div>
            </div>
        </div>
    </form>
</div>

</div>
</div>
</div>

<!-- FILTER PANEL -->
<div
    x-show="filtersOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-3 scale-y-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
    x-transition:leave-end="opacity-0 -translate-y-2 scale-y-95"
    class="pb-4 origin-top"
    style="background-color: rgb(215, 183, 142)"
>
    <div class="w-full pt-2 px-2 sm:px-3">
        <form method="GET"
              action="{{ route('search.listings') }}"
              class="grid grid-cols-1 sm:grid-cols-5 gap-4 border-0 shadow-none outline-none"
              style="background-color: rgb(215, 183, 142)">

            <input type="hidden" name="q" value="{{ request('q') }}">

            <!-- Category -->
            <div class="relative" x-data="{ open: false, selected: '{{ request('category_id', '') }}' }" @keydown.escape.window="open = false">
                <input type="hidden" name="category_id" :value="selected">

                <button
                    type="button"
                    @click.stop="open = !open"
                    :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                    class="w-full rounded border py-2 px-3 text-left focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                    style="background-color: rgb(234, 220, 200)"
                >
                    <span class="text-black"
                        x-text="selected === '' ? 'Kategorija' : (() => {
                            const categories = {
                                @foreach(\App\Models\Category::all() as $cat)
                                    '{{ $cat->id }}': '{{ $cat->pavadinimas }}',
                                @endforeach
                            };
                            return categories[selected] ?? 'Kategorija';
                        })()"
                    ></span>

                    <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition
                    @click.outside="open = false"
                    class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50 max-h-60 overflow-y-auto"
                    style="background-color: rgb(234, 220, 200); border-color: #836354"
                >
                    <div
                        @click="selected = ''; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        Kategorija
                    </div>

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

            <!-- Type -->
            <div class="relative" x-data="{ open: false, selected: '{{ request('tipas', '') }}' }" @keydown.escape.window="open = false">
                <input type="hidden" name="tipas" :value="selected">

                <button
                    type="button"
                    @click.stop="open = !open"
                    :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                    class="w-full rounded border py-2 px-3 text-left focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                    style="background-color: rgb(234, 220, 200)"
                >
                    <span class="text-black" x-text="selected === '' ? 'Tipas' : (selected === 'preke' ? 'Prekė' : 'Paslauga')"></span>

                    <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition
                    @click.outside="open = false"
                    class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50"
                    style="background-color: rgb(234, 220, 200); border-color: #836354"
                >
                    <div
                        @click="selected = ''; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        Tipas
                    </div>

                    <div
                        @click="selected = 'preke'; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        Prekė
                    </div>

                    <div
                        @click="selected = 'paslauga'; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        Paslauga
                    </div>
                </div>
            </div>

            <!-- Min Price -->
            <input
                type="number"
                name="min_price"
                class="border border-gray-500 rounded px-3 py-2 text-black placeholder-gray-700 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                placeholder="Min. kaina"
                value="{{ request('min_price') }}"
               min="0.20"
               max="99999"
               step="0.01"
            >

            <!-- Max Price -->
            <input
                type="number"
                name="max_price"
                class="border border-gray-500 rounded px-3 py-2 text-black placeholder-gray-700 focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354]"
                style="background-color: rgb(234, 220, 200)"
                placeholder="Maks. kaina"
                value="{{ request('max_price') }}"
               min="0.20"
               max="99999"
               step="0.01"
            >

            <!-- City -->
            <div class="relative" x-data="{ open: false, selected: '{{ request('city_id', '') }}' }" @keydown.escape.window="open = false">
                <input type="hidden" name="city_id" :value="selected">

                <button
                    type="button"
                    @click.stop="open = !open"
                    :class="open ? 'ring-1 ring-[#836354] border-[#836354]' : 'border-gray-500'"
                    class="w-full rounded border py-2 px-3 text-left focus:outline-none focus:ring-1 focus:ring-[#836354] focus:border-[#836354] flex justify-between items-center"
                    style="background-color: rgb(234, 220, 200)"
                >
                    <span class="text-black"
                        x-text="selected === '' ? 'Miestas' : (() => {
                            const cities = {
                                @foreach(\App\Models\City::orderBy('pavadinimas')->get() as $city)
                                    '{{ $city->id }}': '{{ $city->pavadinimas }}',
                                @endforeach
                            };
                            return cities[selected] ?? 'Miestas';
                        })()"
                    ></span>

                    <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition
                    @click.outside="open = false"
                    class="absolute left-0 right-0 mt-1 rounded border shadow overflow-hidden z-50 max-h-60 overflow-y-auto"
                    style="background-color: rgb(234, 220, 200); border-color: #836354"
                >
                    <div
                        @click="selected = ''; open = false"
                        class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                    >
                        Miestas
                    </div>

                    @foreach(\App\Models\City::orderBy('pavadinimas')->get() as $city)
                        <div
                            @click="selected = '{{ $city->id }}'; open = false"
                            class="block w-full px-3 py-2 text-black cursor-pointer hover:bg-[#cfae86]"
                        >
                            {{ $city->pavadinimas }}
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Submit -->
            <div class="col-span-full flex flex-wrap gap-3 pt-1">
                <button
                    class="text-white px-4 py-2 rounded w-32 hover:text-black"
                    style="background-color: rgb(131, 99, 84)"
                >
                    Taikyti
                </button>

                <a
                    href="{{ route('search.listings') }}"
                    class="text-white px-4 py-2 rounded w-32 text-center hover:text-black"
                    style="background-color: rgb(184, 80, 54)"
                >
                    Išvalyti
                </a>
            </div>

        </form>
              
        </div>
    </div>
@endif
</nav>
