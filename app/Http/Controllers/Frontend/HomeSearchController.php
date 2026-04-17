<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ListingService;

class HomeSearchController extends Controller
{
    protected ListingService $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    public function search(\Illuminate\Http\Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:category,id'],
            'tipas' => ['nullable', 'in:preke,paslauga'],
            'city_id' => ['nullable', 'integer', 'exists:city,id'],
            'sort' => ['nullable', 'in:newest,oldest,price_asc,price_desc'],
            'min_price' => ['nullable', 'numeric', 'min:0.20', 'max:99999'],
            'max_price' => ['nullable', 'numeric', 'min:0.20', 'max:99999'],
        ], [
            'min_price.min' => 'Minimali kaina turi būti bent 0,20.',
            'min_price.max' => 'Minimali kaina negali būti didesnė nei 99999.',
            'max_price.min' => 'Maksimali kaina turi būti bent 0,20.',
            'max_price.max' => 'Maksimali kaina negali būti didesnė nei 99999.',
        ]);
    
        if (
            isset($filters['min_price'], $filters['max_price']) &&
            (float) $filters['min_price'] > (float) $filters['max_price']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'max_price' => 'Maksimali kaina turi būti didesnė arba lygi minimaliai kainai.'
                ]);
        }
    
        $listings = $this->listingService->search($filters);
    
        return view('frontend.search-results', [
            'listings' => $listings,
            'filters'  => $filters,
        ]);
    }
}
