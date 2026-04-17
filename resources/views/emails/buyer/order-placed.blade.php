@component('mail::message')
# Jūsų užsakymas pateiktas!

Sveiki, {{ $order->user->vardas }},

Ačiū už jūsų pirkinį!  
Jūsų užsakymas **#{{ $order->id }}** sėkmingai pateiktas.

---

## Užsakymo prekės

@component('mail::table')
|  | Prekė  | Suma |
|:--|:-----|------:|
@foreach($order->orderItem as $item)
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
@endif
| **{{ $item->listing->pavadinimas }}**  
€{{ number_format($item->kaina, 2) }} × {{ $item->kiekis }}
| **€{{ number_format($item->kaina * $item->kiekis, 2) }}**
@endforeach
@endcomponent

---

## Užsakymo suvestinė

<table width="100%" cellpadding="4" cellspacing="0">
<tr>
    <td>Prekių suma</td>
    <td align="right">
        €{{ number_format($order->bendra_suma, 2) }}
    </td>
</tr>

@if($order->small_order_fee_cents > 0)
<tr>
    <td>Mažo užsakymo mokestis</td>
    <td align="right">
        €{{ number_format($order->small_order_fee_cents / 100, 2) }}
    </td>
</tr>
@endif

@if($order->shipping_total_cents > 0)
<tr>
    <td>Pristatymas</td>
    <td align="right">
        €{{ number_format($order->shipping_total_cents / 100, 2) }}
    </td>
</tr>
@endif

<tr>
    <td colspan="2"><hr></td>
</tr>

<tr>
    <td><strong>Iš viso sumokėta</strong></td>
    <td align="right">
        <strong>
            €{{ number_format($order->amount_charged_cents / 100, 2) }}
        </strong>
    </td>
</tr>
</table>

---

## Pristatymo adresas
@php
    $shipping = is_array($order->shipping_address)
        ? $order->shipping_address
        : (json_decode($order->shipping_address ?? '[]', true) ?: []);
@endphp

@if(!empty($shipping))
{{ $shipping['address'] ?? '' }}
@if(!empty($shipping['city']) || !empty($shipping['country']))
{{ !empty($shipping['address']) ? '  ' : '' }}{{ $shipping['city'] ?? '' }}@if(!empty($shipping['city']) && !empty($shipping['country'])),@endif {{ $shipping['country'] ?? '' }}
@endif
@if(!empty($shipping['postal_code']))
  {{ $shipping['postal_code'] }}
@endif
@elseif($order->address && $order->address->city)
{{ $order->address->gatve ?? '' }}
{{ $order->address->city->pavadinimas }},
{{ $order->address->city->country->pavadinimas }}
@endif

---

 Kai prekės bus išsiųstos, gausite dar vieną el. laišką.

Ačiū, kad apsiperkate pas mus,
{{ config('app.name') }}
@endcomponent
