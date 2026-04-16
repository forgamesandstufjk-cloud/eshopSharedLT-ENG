<x-app-layout>
    <div class="min-h-screen w-full px-4 mt-10 pb-10 relative" style="background-color: rgb(234, 220, 200);">
        <div class="max-w-2xl mx-auto relative z-10">
            <div class="shadow rounded p-8 text-center" style="background-color: rgb(215, 183, 142);">
                <h1 class="text-3xl font-bold mb-3 text-black">Mokėjimas priimtas</h1>

                <p class="text-black mb-6">
                    Mokėjimas apdorojamas. Patvirtintą būseną matysite savo pirkimų sąraše.
                </p>

                <a href="{{ $isService ? route('buyer.orders') : route('buyer.orders') }}"
                   class="inline-block text-white px-6 py-3 rounded hover:text-black"
                   style="background-color: rgb(131, 99, 84);">
                    Grįžti į mano pirkimus
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
