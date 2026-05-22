<p>Sveiki,</p>

<p>Atsiprašome, pardavėjas nepateikė siuntos įrodymo per numatytą laiką.</p>

<p>Dėl to Jums buvo atliktas grąžinimas už šią siuntą.</p>

<p>
    Užsakymas #{{ $shipment->order_id }}<br>
    Grąžinta suma: €{{ number_format(($shipment->refund_amount_cents ?? 0) / 100, 2) }}
</p>

<p>Atsiprašome už nepatogumus.</p>