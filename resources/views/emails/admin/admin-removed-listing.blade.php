<p>Sveiki, {{ $sellerName }},</p>

<p>Jūsų skelbimas <strong>{{ $listingTitle }}</strong> buvo pašalintas administratoriaus.</p>

<p><strong>Priežastis:</strong> {{ $reason }}</p>

@if($adminNote)
    <p><strong>Administratoriaus pastaba:</strong> {{ $adminNote }}</p>
@endif

<p>Jei manote, kad tai įvyko per klaidą, susisiekite su administracija.</p>
