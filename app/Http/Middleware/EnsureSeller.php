<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSeller
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $missing = [];

        if ($user->role !== 'seller') {
            $missing[] = 'Pažymėkite, kad esate pardavėjas / verslas.';
        }

        if (!$user->business_email && !$user->telefonas) {
            $missing[] = 'Įveskite bent vieną viešą kontaktinę informaciją: verslo el. paštą arba telefoną.';
        }

        if (!$user->address || !$user->address->city_id) {
            $missing[] = 'Pasirinkite šalį ir miestą.';
        }

        if (!$user->stripe_account_id || !$user->stripe_onboarded) {
            $missing[] = 'Prijunkite Stripe paskyrą.';
        }

        if (!empty($missing)) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Norėdami įkelti skelbimą, užpildykite trūkstamą informaciją.')
                ->with('missing_seller_requirements', $missing);
        }

        return $next($request);
    }
}
