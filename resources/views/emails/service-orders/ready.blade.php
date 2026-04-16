<p>Sveiki, {{ $serviceOrder->buyer->vardas ?? $serviceOrder->buyer->name ?? 'pirkėjau' }},</p>

<p>Jūsų paslaugos užsakymas yra paruoštas.</p>

<p>
    <strong>Užsakymas:</strong> #{{ $serviceOrder->id }}<br>
    <strong>Skelbimas:</strong> {{ $serviceOrder->original_listing_title }}<br>
    <strong>Kaina:</strong> €{{ number_format((float) $serviceOrder->final_price, 2) }}
</p>

<p>
    Susisiekite su pardavėju dėl galutinio užbaigimo būdo:
    per svetainę arba privačiai.
</p>

<p>Ačiū.</p>
