@component('mail::message')
# Naujas pardavimas!

Sveiki, {{ $seller->vardas }},

Turite naują pardavimą užsakyme **Nr. {{ $order->id }}**.

---

## Prekės, kurias turite išsiųsti

@foreach($items as $it)
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
<tr>
<td style="vertical-align:top;">
<strong>{{ $it['title'] }}</strong> × {{ $it['qty'] }}
</td>

<td align="right" style="width:80px;">
@if(!empty($it['image']))
<img
    src="{{ $it['image'] }}"
    width="70"
    height="70"
    style="display:block;border-radius:6px;border:1px solid #ddd;"
    alt="{{ $it['title'] }}"
>
@endif
</td>
</tr>
</table>
@endforeach

---

## Pristatymo adresas

@if(!empty($shipping['address_line']))
**Adresas:** {{ $shipping['address_line'] }}  
@endif

@if(!empty($shipping['city']))
**Miestas:** {{ $shipping['city'] }}  
@endif

@if(!empty($shipping['country']))
**Šalis:** {{ $shipping['country'] }}  
@endif

@if(!empty($shipping['postal_code']))
**Pašto kodas:** {{ $shipping['postal_code'] }}  
@endif

---

## Pristatymo būdas

@if(!empty($shipping['carrier']))
**Būdas:** {{ $shipping['carrier'] }}  
@endif

@if(!empty($shipping['package_size']))
**Siuntos dydis:** {{ $shipping['package_size'] }}  
@endif

---

## Išsiuntimo terminas

**{{ $shipping['deadline'] }}**

---

@component('mail::button', ['url' => $shipping['shipments_url']])
Tvarkyti siuntą
@endcomponent

Ačiū,  
{{ config('app.name') }}
@endcomponent
