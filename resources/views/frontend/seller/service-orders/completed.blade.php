<x-app-layout>
    <div class="min-h-screen w-full max-w-6xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0 pb-10" style="background-color: rgb(234, 220, 200);">
        <div class="container mx-auto relative z-10">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-black">Užbaigti paslaugų užsakymai</h1>

                <a href="{{ route('seller.service-orders.index') }}"
                   class="px-4 py-2 rounded text-white"
                   style="background-color: rgb(131, 99, 84)">
                    Grįžti į lentą
                </a>
            </div>

            <div class="space-y-4">
                @forelse($serviceOrders as $order)
                    <div class="shadow rounded p-4 text-black" style="background-color: rgb(215, 183, 142)">
                        <div class="flex justify-between gap-4">
                            <div>
                                <div class="font-semibold">#{{ $order->id }} – {{ $order->original_listing_title }}</div>
                                <div>Būsena: {{ $order->lithuanian_status }}</div>
                                <div>Užbaigimo būdas:
                                    {{ $order->completion_method === 'private' ? 'Privačiai' : 'Per svetainę' }}
                                </div>
                            </div>
                            <div class="font-semibold">€{{ number_format((float) $order->final_price, 2) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="shadow rounded p-4 text-black" style="background-color: rgb(215, 183, 142)">
                        Užbaigtų paslaugų užsakymų nėra.
                    </div>
                @endforelse
            </div>

            <div class="mt-6 text-black">
                {{ $serviceOrders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
