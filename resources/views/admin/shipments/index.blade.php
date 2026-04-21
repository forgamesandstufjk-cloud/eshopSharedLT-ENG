<x-app-layout>
    <x-slot name="title">Siuntų moderavimas</x-slot>
    <div class="min-h-screen w-full max-w-6xl mx-auto mt-6 sm:mt-10 px-3 sm:px-0 pb-10" style="background-color: rgb(234, 220, 200)">
        <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-black">Siuntų moderavimas</h1>

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

        {{-- PAPRASTOS SIUNTOS --}}
        <div class="shadow rounded overflow-hidden" style="background-color: rgb(215, 183, 142)">
            <table class="w-full text-sm text-black">
                <thead class="border-b hidden sm:table-header-group" style="background-color: rgb(131, 99, 84); border-color: #836354">
                    <tr>
                        <th class="p-3 text-left text-white">Užsakymas</th>
                        <th class="p-3 text-left text-white">Pardavėjas</th>
                        <th class="p-3 text-left text-white">Pirkėjas</th>
                        <th class="p-3 text-left text-white">Vežėjas</th>
                        <th class="p-3 text-left text-white">Įrodymas</th>
                        <th class="p-3 text-left text-white">Siuntos sekimas</th>
                        <th class="p-3 text-left text-white">Veiksmai</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($shipments as $s)
                    <tr class="block sm:table-row border-b" style="border-color: #836354;">
                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Užsakymas: </span>
                            #{{ $s->order_id }}
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Pardavėjas: </span>
                            {{ $s->seller->name ?? $s->seller->vardas }}
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Pirkėjas: </span>
                            {{ $s->order->user->name ?? $s->order->user->vardas }}
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Vežėjas: </span>
                            {{ strtoupper($s->carrier) }} ({{ $s->package_size }})
                            <br>
                            €{{ number_format($s->shipping_cents / 100, 2) }}
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Įrodymas: </span>
                            @if($s->proof_path)
                                @php
                                    $proofUrl = \Illuminate\Support\Facades\Storage::disk('photos')->exists($s->proof_path)
                                        ? \Illuminate\Support\Facades\Storage::disk('photos')->url($s->proof_path)
                                        : \Illuminate\Support\Facades\Storage::disk('public')->url($s->proof_path);
                                @endphp
                                <a href="{{ $proofUrl }}"
                                   target="_blank"
                                   class="underline"
                                   style="color: rgb(131, 99, 84)">
                                    Peržiūrėti įrodymą
                                </a>
                            @else
                                —
                            @endif
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Siuntos sekimas: </span>
                            {{ $s->tracking_number ?? '—' }}
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <form method="POST" action="{{ route('admin.shipments.approve', $s) }}">
                                    @csrf
                                    <button class="text-white px-3 py-1 rounded w-full sm:w-auto hover:text-black"
                                            style="background-color: rgb(131, 99, 84)">
                                        Patvirtinti
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.shipments.reject', $s) }}">
                                    @csrf
                                    <button class="text-white px-3 py-1 rounded w-full sm:w-auto hover:text-black"
                                            style="background-color: rgb(184, 80, 54)">
                                        Atmesti
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-black">
                            Nėra siuntų, laukiančių peržiūros.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-black">
            {{ $shipments->links() }}
        </div>

        {{-- PASLAUGŲ UŽSAKYMŲ SIUNTOS --}}
        <div class="shadow rounded overflow-hidden mt-8" style="background-color: rgb(215, 183, 142)">
            <div class="p-4 font-bold text-black">Paslaugų užsakymų siuntų peržiūra</div>

            <table class="w-full text-sm text-black">
                <thead class="border-b hidden sm:table-header-group" style="background-color: rgb(131, 99, 84); border-color: #836354">
                    <tr>
                        <th class="p-3 text-left text-white">Užsakymas</th>
                        <th class="p-3 text-left text-white">Pardavėjas</th>
                        <th class="p-3 text-left text-white">Pirkėjas</th>
                        <th class="p-3 text-left text-white">Vežėjas</th>
                        <th class="p-3 text-left text-white">Įrodymas</th>
                        <th class="p-3 text-left text-white">Siuntos sekimas</th>
                        <th class="p-3 text-left text-white">Veiksmai</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($serviceShipments as $s)
                    <tr class="block sm:table-row border-b" style="border-color: #836354;">
                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Užsakymas: </span>
                            #{{ $s->id }}
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Pardavėjas: </span>
                            {{ $s->seller->name ?? $s->seller->vardas }}
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Pirkėjas: </span>
                            @if($s->buyer)
                                {{ $s->buyer->name ?? $s->buyer->vardas }}
                            @elseif($s->is_anonymous)
                                Nenurodytas
                            @else
                                —
                            @endif
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Vežėjas: </span>
                            {{ strtoupper($s->carrier ?? '—') }} ({{ $s->package_size ?? '—' }})
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Įrodymas: </span>
                            @if($s->proof_path)
                                @php
                                    $proofUrl = \Illuminate\Support\Facades\Storage::disk('photos')->exists($s->proof_path)
                                        ? \Illuminate\Support\Facades\Storage::disk('photos')->url($s->proof_path)
                                        : \Illuminate\Support\Facades\Storage::disk('public')->url($s->proof_path);
                                @endphp
                                <a href="{{ $proofUrl }}"
                                   target="_blank"
                                   class="underline"
                                   style="color: rgb(131, 99, 84)">
                                    Peržiūrėti įrodymą
                                </a>
                            @else
                                —
                            @endif
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <span class="font-semibold sm:hidden">Siuntos sekimas: </span>
                            {{ $s->tracking_number ?? '—' }}
                        </td>

                        <td class="p-3 block sm:table-cell">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <form method="POST" action="{{ route('admin.service-shipments.approve', $s) }}">
                                    @csrf
                                    <button class="text-white px-3 py-1 rounded w-full sm:w-auto hover:text-black"
                                            style="background-color: rgb(131, 99, 84)">
                                        Patvirtinti
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.service-shipments.reject', $s) }}">
                                    @csrf
                                    <button class="text-white px-3 py-1 rounded w-full sm:w-auto hover:text-black"
                                            style="background-color: rgb(184, 80, 54)">
                                        Atmesti
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-black">
                            Nėra paslaugų užsakymų siuntų, laukiančių peržiūros.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-black">
            {{ $serviceShipments->links() }}
        </div>
    </div>
</x-app-layout>
