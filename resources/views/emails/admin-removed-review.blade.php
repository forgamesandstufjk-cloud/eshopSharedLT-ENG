<p>Sveiki, {{ $review->user->vardas }} {{ $review->user->pavarde }},</p>

<p>Jūsų komentaras / atsiliepimas apie skelbimą <strong>{{ $review->Listing->pavadinimas ?? '—' }}</strong> buvo pašalintas administratoriaus.</p>

<p><strong>Priežastis:</strong> {{ $adminNote }}</p>

<p>Jei manote, kad tai įvyko per klaidą, susisiekite su administracija atsakydami į šį laišką.</p>
