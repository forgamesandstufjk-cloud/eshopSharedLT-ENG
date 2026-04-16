<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ListingService;
use Illuminate\Support\Facades\Auth;
use App\Models\Listing;

class MyListingsController extends Controller
{
    protected ListingService $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
        $this->middleware('auth');
    }

    public function index()
    {
        if (auth()->user()->isBannedUser()) {
            return redirect()->route('home')->with('error', 'Jūsų paskyra apribota. Skelbimų valdyti negalite.');
        }
        
        $userId = auth()->id();

        $listings = Listing::with('photos')
            ->where('user_id', $userId)
            ->where('is_hidden', 0)
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('frontend.my-listings', compact('listings'));
    }
}
