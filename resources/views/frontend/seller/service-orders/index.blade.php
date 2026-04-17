<x-app-layout>
    <div class="min-h-screen w-full max-w-7xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0 pb-10" style="background-color: rgb(234, 220, 200)">
        <div class="container mx-auto relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-black">Paslaugų užsakymų lenta</h1>

                <div class="flex gap-2">
                    <a href="{{ route('seller.service-orders.index', ['view' => 'completed']) }}"
                       class="px-4 py-2 rounded text-black"
                       style="background-color: rgb(215, 183, 142)">
                        Užbaigti
                    </a>

                    <a href="{{ route('seller.service-orders.create') }}"
                       class="px-4 py-2 rounded text-white"
                       style="background-color: rgb(131, 99, 84)">
                        Sukurti užsakymą
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(207, 174, 134)">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(230, 190, 190)">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                @php
                    $columns = [
                        'Sutarta' => $agreedOrders,
                        'Daroma' => $daromasOrders,
                        'Paruošta išsiuntimui' => $readyOrders,
                    ];
                @endphp

                @foreach($columns as $columnTitle => $orders)
                    <div class="rounded p-3" style="background-color: rgb(215, 183, 142)">
                        <h2 class="font-bold text-black mb-3">{{ $columnTitle }}</h2>

                        <div class="space-y-3">
                            @forelse($orders as $order)
                                <div class="rounded p-3 shadow" style="background-color: rgb(234, 220, 200)">
                                    <div class="flex justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-black">
                                                #{{ $order->id }} – {{ $order->original_listing_title }}
                                            </div>

                                            <div class="text-sm text-black">
                                                Pirkėjas:
                                                @if($order->is_anonymous)
                                                    Nenurodytas
                                                @elseif($order->buyer)
                                                    {{ $order->buyer->vardas ?? $order->buyer->name }}
                                                @elseif($order->buyer_code_snapshot)
                                                    Kodas: {{ $order->buyer_code_snapshot }}
                                                @else
                                                    Nepriskirtas
                                                @endif
                                            </div>

                                            <div class="text-sm text-black">
                                                €{{ number_format((float) $order->final_price, 2) }}
                                            </div>

                                            <div class="text-sm text-black">
                                                Siuntos dydis: {{ $order->package_size ?: '—' }}
                                            </div>

                                            @if($order->status === \App\Models\ServiceOrder::STATUS_READY_TO_SHIP)
                                                <div class="mt-2">
                                                    @if($order->completion_method === \App\Models\ServiceOrder::COMPLETION_PLATFORM)
                                                        @if($order->payment_status === \App\Models\ServiceOrder::PAYMENT_PAID)
                                                            <span class="px-2 py-1 rounded text-xs text-black" style="background-color: rgb(207, 174, 134)">
                                                                Norint gauti pinigus pateikite siuntos įrodymą
                                                            </span>
                                                        @else
                                                        @endif
                                                    @elseif($order->completion_method === \App\Models\ServiceOrder::COMPLETION_PRIVATE)
                                                        <span class="px-2 py-1 rounded text-xs text-black" style="background-color: rgb(207, 174, 134)">
                                                            Bus užbaigta privačiai
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 rounded text-xs text-black" style="background-color: rgb(207, 174, 134)">
                                                            Laukiama užbaigimo būdo pasirinkimo
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a href="{{ route('seller.service-orders.show', $order) }}"
                                           class="px-3 py-2 rounded text-sm text-white"
                                           style="background-color: rgb(131, 99, 84)">
                                            Atidaryti
                                        </a>

                                        <a href="{{ route('seller.service-orders.edit', $order) }}"
                                           class="px-3 py-2 rounded text-sm text-white"
                                           style="background-color: rgb(131, 99, 84)">
                                            Redaguoti
                                        </a>

                                        @if($order->status === \App\Models\ServiceOrder::STATUS_AGREED)
                                            <form method="POST" action="{{ route('seller.service-orders.status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="daromas">
                                                <button class="px-3 py-2 rounded text-sm text-white" style="background-color: rgb(131, 99, 84)">
                                                    Daroma ->
                                                </button>
                                            </form>
                                        @endif

                                        @if($order->status === \App\Models\ServiceOrder::STATUS_DAROMAS)
                                            <form method="POST" action="{{ route('seller.service-orders.status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="agreed">
                                                <button class="px-3 py-2 rounded text-sm text-white" style="background-color: rgb(131, 99, 84)">
                                                    <- Grąžinti į sutarta
                                                </button>
                                            </form>

                                            <form method="POST"
                                                  action="{{ route('seller.service-orders.status', $order) }}"
                                                  onsubmit="return confirm('Ar tikrai norite perkelti į „Paruošta išsiuntimui“? Po šio veiksmo užsakymo nebegalėsite grąžinti atgal į „Daroma“.');">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="ready_to_ship">
                                                <button class="px-3 py-2 rounded text-sm text-white" style="background-color: rgb(131, 99, 84)">
                                                    Paruošta išsiuntimui ->
                                                </button>
                                            </form>
                                        @endif

                                        @if($order->status === \App\Models\ServiceOrder::STATUS_READY_TO_SHIP)
                                            @if($order->completion_method === null)
                                                <form method="POST" action="{{ route('seller.service-orders.choose-platform', $order) }}">
                                                    @csrf
                                                    <button class="px-3 py-2 rounded text-sm text-white" style="background-color: rgb(131, 99, 84)">
                                                        Atsiskaitymas per svetainę
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                      action="{{ route('seller.service-orders.choose-private', $order) }}"
                                                      onsubmit="return confirm('Ar tikrai norite užbaigti privačiai?')">
                                                    @csrf
                                                    <button class="px-3 py-2 rounded text-sm text-white" style="background-color: rgb(184, 80, 54)">
                                                        Užbaigti privačiai
                                                    </button>
                                                </form>

                                            @elseif(
                                                $order->completion_method === \App\Models\ServiceOrder::COMPLETION_PLATFORM &&
                                                $order->payment_status !== \App\Models\ServiceOrder::PAYMENT_PAID
                                            )
                                                <div class="px-3 py-2 rounded text-sm text-black" style="background-color: rgb(207, 174, 134)">
                                                    Laukiama pirkėjo apmokėjimo
                                                </div>

                                                <form method="POST"
                                                      action="{{ route('seller.service-orders.choose-private', $order) }}"
                                                      onsubmit="return confirm('Ar tikrai norite perjungti į privatų užbaigimą? Tai padarius nebebus galima užbaigti per svetainę.');">
                                                    @csrf
                                                    <button class="px-3 py-2 rounded text-sm text-white" style="background-color: rgb(184, 80, 54)">
                                                        Užbaigti privačiai
                                                    </button>
                                                </form>

                                            @elseif(
                                                $order->completion_method === \App\Models\ServiceOrder::COMPLETION_PLATFORM &&
                                                $order->payment_status === \App\Models\ServiceOrder::PAYMENT_PAID
                                            )
                                                <a href="{{ route('seller.orders') }}#service-order-{{ $order->id }}"
                                                   class="px-3 py-2 rounded text-sm text-white"
                                                   style="background-color: rgb(131, 99, 84)">
                                                    Tęsti per svetainę
                                                </a>

                                            @elseif($order->completion_method === \App\Models\ServiceOrder::COMPLETION_PRIVATE)
                                                <form method="POST"
                                                      action="{{ route('seller.service-orders.complete-private', $order) }}"
                                                      onsubmit="return confirm('Ar tikrai norite užbaigti privačiai?');">
                                                    @csrf
                                                    <button class="px-3 py-2 rounded text-sm text-white" style="background-color: rgb(184, 80, 54)">
                                                        Užbaigti privačiai
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        @if(in_array($order->status, [
                                            \App\Models\ServiceOrder::STATUS_AGREED,
                                            \App\Models\ServiceOrder::STATUS_DAROMAS
                                        ], true))
                                            <form method="POST" action="{{ route('seller.service-orders.status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button class="px-3 py-2 rounded text-sm text-white" style="background-color: rgb(184, 80, 54)">
                                                    Atšaukti
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="rounded p-4 text-sm text-black" style="background-color: rgb(234, 220, 200)">
                                    Įrašų nėra.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
