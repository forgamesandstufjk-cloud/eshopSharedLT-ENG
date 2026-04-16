@component('mail::message')
# Siunta laukia patvirtinimo

Pardavėjas pateikė siuntos įrodymą, kurį reikia peržiūrėti.

---

@if($isServiceOrder)
## Paslaugos užsakymas
**#{{ $shipment->id }}**
@else
## Užsakymas
**#{{ $shipment->order_id }}**
@endif

## Pardavėjas
{{ $shipment->seller->vardas ?? $shipment->seller->name }}  
ID: {{ $shipment->seller_id }}

@if($isServiceOrder)
## Tipas
Paslaugos užsakymas
@else
## Tipas
Įprasta siunta
@endif

---

@component('mail::button', ['url' => route('admin.shipments.index')])
Peržiūrėti siuntas
@endcomponent

Ši siunta turi būti patvirtinta arba atmesta prieš galutinį užbaigimą.

{{ config('app.name') }}
@endcomponent
