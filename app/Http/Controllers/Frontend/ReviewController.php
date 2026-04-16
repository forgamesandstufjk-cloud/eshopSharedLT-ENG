<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function store(Request $request, $listingId)
    {
        if (auth()->user()->isBannedUser()) {
        return back()->with('error', 'Jūsų paskyra apribota. Negalite palikti atsiliepimų.');
    }
        
        $data = $request->validate([
            'ivertinimas' => 'required|integer|min:1|max:5',
            'komentaras'  => 'nullable|string|max:2000',
        ]);

        $data['listing_id'] = $listingId;
        $data['user_id'] = auth()->id();

        try {
            $this->reviewService->create($data);
            return back()->with('success', 'Įvertinimas išsaugotas!');
        }
        
       catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage()); 
        }
    }
}
