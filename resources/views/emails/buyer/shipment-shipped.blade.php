@component('mail::message')
# Jūsų užsakymas jau pakeliui

Sveiki, {{ $shipment->order->user->vardas }},

 Gera žinia! Dalis Jūsų užsakymo **#{{ $shipment->order_id }}** buvo išsiųsta.

---

## Išsiųstos prekės
@foreach($shipment->order->orderItem as $item)
@if($item->listing->user_id === $shipment->seller_id)
{!! '
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
    <tr>
        <td style="vertical-align:middle;">
            <strong>' . e($item->listing->pavadinimas) . '</strong><br>
            <span style="color:#6b7280;">Kiekis: ' . e($item->kiekis) . '</span>
        </td>
        <td align="right" width="70" style="vertical-align:middle; width:70px;">
            ' . (
                $item->listing->photos->isNotEmpty()
                ? '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="60" height="60" style="width:60px; height:60px; border:1px solid #ddd; border-radius:6px; overflow:hidden; background:#ffffff; margin-left:auto;">
                        <tr>
                            <td align="center" valign="middle" width="60" height="60" style="width:60px; height:60px; text-align:center; vertical-align:middle;">
                                <img
                                    src="' . e(\Illuminate\Support\Facades\Storage::disk('photos')->url($item->listing->photos->first()->failo_url)) . '"
                                    alt="' . e($item->listing->pavadinimas) . '"
                                    style="display:block; max-width:60px; max-height:60px; width:auto; height:auto; margin:0 auto; border:0;"
                                >
                            </td>
                        </tr>
                   </table>'
                : ''
            ) . '
        </td>
    </tr>
</table>
' !!}
@endif
@endforeach

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
