<footer
            class="border-t relative z-50 isolate"
            style="background-color: rgb(215, 183, 142); opacity: 1; border-color: #d6b07a"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 text-black">
                    <div>
                        <h3 class="text-xl font-bold mb-4">Lietuviška prekė</h3>
                        <div class="space-y-2 text-sm leading-7">
                            <p>© {{ now()->year }}. Visos teisės saugomos.</p>
                            <p>Šiauliai Dvaro g. 10, LT-01102</p>
                            <p>Įmonės kodas: 304496867</p>
                            <p>info@keblu.lt</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold mb-4">Informacija</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('home') }}"" class="hover:underline">Siuntimas</a></li>
                            <li><a href="{{ route('home') }}" class="hover:underline">Grąžinimas</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold mb-4">Pirkėjams</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a <a href="{{ route('about') }}" class="hover:underline">Apie mus</a></li>
                            <li><a href="{{ route('home') }}" class="hover:underline">Paieška</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold mb-4">Pardavėjams</h3>
                        <ul class="space-y-2 text-sm mb-6">
                           <li>
                                    <a href="{{ auth()->check() && auth()->user()->role === 'seller' ? route('listing.create') : route('profile.edit') }}"
                                    class="hover:underline">
                                            Prekiaukite per keblu.lt
                                    </a>
                        </li>
                        </ul>

                        <h3 class="text-xl font-bold mb-4">Bendraukime</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('home') }}" class="hover:underline">Facebook</a></li>
                            <li><a href="{{ route('home') }}" class="hover:underline">Instagram</a></li>
                            <li><a href="{{ route('home') }}" class="hover:underline">infoadministracija@keblu.lt</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t flex flex-col sm:flex-row justify-end gap-6 text-sm text-black"
                     style="border-color: #d6b07a;">
                    <a href="{{ route('home') }}" class="hover:underline">Privatumo politika</a>
                    <a href="{{ route('home') }}" class="hover:underline">Pirkimo-pardavimo taisyklės</a>
                </div>
            </div>
        </footer>
