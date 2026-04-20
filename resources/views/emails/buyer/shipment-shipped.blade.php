@component('mail::message')
# Jūsų užsakymas jau pakeliui

Sveiki, {{ $shipment->order->user->vardas }},

 Gera žinia! Dalis Jūsų užsakymo **#{{ $shipment->order_id }}** buvo išsiųsta.

---

## Išsiųstos prekės

@component('mail::table')
|  | Prekė |
|:--|:------|
@foreach($shipment->order->orderItem as $item)
@if($item->listing->user_id === $shipment->seller_id)
|
@if($item->listing->photos->isNotEmpty())
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="60" height="60" style="width:60px; height:60px; border:1px solid #ddd; border-radius:6px; overflow:hidden; background:#ffffff">
    <tr>
        <td align="center" valign="middle" width="60" height="60" style="width:60px; height:60px; text-align:center; vertical-align:middle">
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('photos')->url($item->listing->photos->first()->failo_url) }}"
                alt="{{ $item->listing->pavadinimas }}"
                style="display:block; max-width:60px; max-height:60px; width:auto; height:auto; margin:0 auto; border:0"
            >
        </td>
    </tr>
</table>
@else
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="60" height="60" style="width:60px; height:60px; border:1px solid #ddd; border-radius:6px; overflow:hidden; background:#ffffff">
    <tr>
        <td align="center" valign="middle" width="60" height="60" style="width:60px; height:60px; text-align:center; vertical-align:middle; color:#6b7280; font-size:12px;">
            No image
        </td>
    </tr>
</table>
@endif
| **{{ $item->listing->pavadinimas }}**  
Kiekis: {{ $item->kiekis }}
@endif
@endforeach
@endcomponent

---

@if($shipment->tracking_number)
## Siuntos sekimo numeris
**{{ $shipment->tracking_number }}**
@endif

---

## Pristatymo adresas
@php
    $shippingAddress = is_array($shipment->order->shipping_address)
        ? $shipment->order->shipping_address
        : (json_decode($shipment->order->shipping_address ?? '{}', true) ?: []);
@endphp

## Pristatymo adresas
@if(!empty($shippingAddress['address']) || !empty($shippingAddress['city']) || !empty($shippingAddress['country']) || !empty($shippingAddress['postal_code']))
@if(!empty($shippingAddress['address']))
**Adresas:** {{ $shippingAddress['address'] }}  
@endif

@if(!empty($shippingAddress['city']))
**Miestas:** {{ $shippingAddress['city'] }}  
@endif

@if(!empty($shippingAddress['country']))
**Šalis:** {{ $shippingAddress['country'] }}  
@endif

@if(!empty($shippingAddress['postal_code']))
**Pašto kodas:** {{ $shippingAddress['postal_code'] }}  
@endif
@else
Adresas nenurodytas.
@endif

---
## Pristatymo būdas

**Būdas:** {{ strtoupper($shipment->carrier) }}  
**Siuntos dydis:** {{ $shipment->package_size }}

---

Jei kas nors pasikeis, informuosime Jus papildomai.

Ačiū, kad apsiperkate pas mus, 
{{ config('app.name') }}
@endcomponent
