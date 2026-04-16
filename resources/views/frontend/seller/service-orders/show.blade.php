<x-app-layout>
    <div class="max-w-5xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0 pb-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl sm:text-2xl font-bold text-black">
                Paslaugos užsakymas #{{ $serviceOrder->id }}
            </h1>

            <a href="{{ route('seller.service-orders.index') }}"
               class="text-white px-4 py-2 rounded"
               style="background-color: rgb(131, 99, 84)">
                Grįžti į lentą
            </a>
        </div>

        @if(session('success'))
            <div class="p-3 rounded mb-4 text-black" style="background-color: rgb(207, 174, 134)">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 rounded" style="background-color: rgb(230, 190, 190)">
                <ul class="list-disc pl-5 text-black text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="shadow rounded p-5 mb-5" style="background-color: rgb(215, 183, 142)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-black">
                <div>
                    <div><strong>Skelbimas:</strong> {{ $serviceOrder->original_listing_title }}</div>
                    <div>
                        <strong>Pirkėjas:</strong>
                        @if($serviceOrder->is_anonymous)
                            Nenurodytas
                        @elseif($serviceOrder->buyer)
                            {{ $serviceOrder->buyer->vardas ?? $serviceOrder->buyer->name }}
                        @elseif($serviceOrder->buyer_code_snapshot)
                            Kodas: {{ $serviceOrder->buyer_code_snapshot }}
                        @else
                            Nepriskirtas
                        @endif
                    </div>
                    <div><strong>Būsena:</strong> {{ $serviceOrder->lithuanian_status }}</div>
                    <div><strong>Kaina:</strong> €{{ number_format((float) $serviceOrder->final_price, 2) }}</div>
                </div>

                <div>
                    <div><strong>Sukurta:</strong> {{ $serviceOrder->created_at?->format('Y-m-d H:i') }}</div>
                    <div><strong>Pradėta:</strong> {{ $serviceOrder->started_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    <div><strong>Paruošta išsiuntimui:</strong> {{ $serviceOrder->ready_to_ship_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    <div><strong>Užbaigta:</strong> {{ $serviceOrder->completed_at?->format('Y-m-d H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="shadow rounded p-5 mb-5 text-black" style="background-color: rgb(215, 183, 142)">
            <h2 class="font-semibold mb-3">Detalės</h2>

            <div class="space-y-3 text-sm">
                <div><strong>Pirkėjo informacija:</strong> {{ data_get($serviceOrder->agreed_details, 'buyer_information', '—') }}</div>
                <div><strong>Sutartos specifikacijos:</strong> {{ data_get($serviceOrder->agreed_details, 'agreed_specifications', '—') }}</div>
                <div><strong>Pastabos:</strong> {{ $serviceOrder->notes ?: '—' }}</div>
                <div><strong>Siuntimo pastabos:</strong> {{ $serviceOrder->shipping_notes ?: '—' }}</div>
                <div><strong>Papildomi reikalavimai:</strong> {{ $serviceOrder->custom_requirements ?: '—' }}</div>
                <div><strong>Termino informacija:</strong> {{ $serviceOrder->timeline_notes ?: '—' }}</div>
            </div>
        </div>

        <div class="shadow rounded p-5 text-black" style="background-color: rgb(215, 183, 142)">
    <h2 class="font-semibold mb-3">Veiksmai</h2>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('seller.service-orders.index') }}"
           class="text-white px-4 py-2 rounded"
           style="background-color: rgb(131, 99, 84)">
            Grįžti į lentą
        </a>

        <a href="{{ route('seller.service-orders.edit', $serviceOrder) }}"
           class="text-white px-4 py-2 rounded"
           style="background-color: rgb(131, 99, 84)">
            Redaguoti
        </a>

        @if($serviceOrder->status === \App\Models\ServiceOrder::STATUS_AGREED)
            <form method="POST" action="{{ route('seller.service-orders.status', $serviceOrder) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="daromas">
                <button class="text-white px-4 py-2 rounded" style="background-color: rgb(131, 99, 84)">
                    Perkelti į Daroma
                </button>
            </form>
        @endif

        @if($serviceOrder->status === \App\Models\ServiceOrder::STATUS_DAROMAS)
            <form method="POST" action="{{ route('seller.service-orders.status', $serviceOrder) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="agreed">
                <button class="text-white px-4 py-2 rounded" style="background-color: rgb(131, 99, 84)">
                    Grąžinti į Sutarta
                </button>
            </form>

            <form method="POST"
                  action="{{ route('seller.service-orders.status', $serviceOrder) }}"
                  onsubmit="return confirm('Ar tikrai norite perkelti į „Paruošta išsiuntimui“? Po šio veiksmo užsakymo nebegalėsite grąžinti atgal į „Daroma“.');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="ready_to_ship">
                <button class="text-white px-4 py-2 rounded" style="background-color: rgb(131, 99, 84)">
                    Perkelti į Paruošta išsiuntimui
                </button>
            </form>
        @endif

        @if($serviceOrder->status === \App\Models\ServiceOrder::STATUS_READY_TO_SHIP)
            <a href="{{ route('seller.orders') }}#service-order-{{ $serviceOrder->id }}"
               class="text-white px-4 py-2 rounded"
               style="background-color: rgb(131, 99, 84)">
                Tęsti per svetainę
            </a>

            <form method="POST"
                  action="{{ route('seller.service-orders.complete-private', $serviceOrder) }}"
                  onsubmit="return confirm('Ar tikrai norite užbaigti privačiai? Tokiu atveju svetainė neturės siuntos įrodymų ir negalės padėti ginčų, grąžinimų ar kitų nesutarimų atveju.');">
                @csrf
                <button class="text-white px-4 py-2 rounded" style="background-color: rgb(184, 80, 54)">
                    Užbaigti privačiai
                </button>
            </form>
        @endif

        @if(in_array($serviceOrder->status, [
            \App\Models\ServiceOrder::STATUS_AGREED,
            \App\Models\ServiceOrder::STATUS_DAROMAS,
            \App\Models\ServiceOrder::STATUS_READY_TO_SHIP
        ], true))
            <form method="POST" action="{{ route('seller.service-orders.status', $serviceOrder) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button class="text-white px-4 py-2 rounded" style="background-color: rgb(184, 80, 54)">
                    Atšaukti
                </button>
            </form>
        @endif
    </div>
</div>
        
    </div>
</x-app-layout>
