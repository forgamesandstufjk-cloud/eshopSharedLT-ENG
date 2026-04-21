<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\ListingService;

class ListingController extends Controller
{
    public function destroy(Listing $listing, ListingService $listingService)
    {
        if (auth()->user()->isBannedUser()) {
            return redirect()
                ->route('home')
                ->with('error', 'Jūsų paskyra apribota. Negalite valdyti skelbimų.');
        }
        
        if ($listing->user_id !== auth()->id()) {
            abort(403);
        }

        $result = $listingService->delete($listing);

        return redirect()
            ->route('my.listings')
            ->with(
                'success',
                $result === 'hidden'
                    ? 'Skelbimas panaikintas.'
                    : 'Skelbimas ištrintas.'
            );
    }
}
