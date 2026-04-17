<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ListingService;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class ListingCreateController extends Controller
{
    protected ListingService $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    public function create()
    {
        if (auth()->user()->isBannedUser()) {
            return redirect()
                ->route('home')
                ->with('error', 'Jūsų paskyra apribota. Negalite kurti skelbimų.');
        }
        
        $categories = Category::all();
        return view('frontend.listing-create', compact('categories'));
    }

   public function store(\App\Http\Requests\StoreListingRequest $request)
{
    if (auth()->user()->isBannedUser()) {
        return redirect()
            ->route('home')
            ->with('error', 'Jūsų paskyra apribota. Negalite kurti skelbimų.');
    }

    $data = $request->validated();

    $data['user_id'] = auth()->id();
    $data['statusas'] = 'aktyvus';
    $data['is_renewable'] = $request->has('is_renewable') ? 1 : 0;

    $listing = $this->listingService->create($data);

    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('listing_photos', 'photos');

            \App\Models\ListingPhoto::create([
                'listing_id' => $listing->id,
                'failo_url' => $path,
            ]);
        }
    }

    return redirect()
        ->route('my.listings')
        ->with('success', 'Skelbimas sėkmingai sukurtas.');
}

    public function edit(Listing $listing)
    {
        if (auth()->user()->isBannedUser() && auth()->user()->role !== 'admin') {
            return redirect()
                ->route('home')
                ->with('error', 'Jūsų paskyra apribota. Negalite redaguoti skelbimų.');
        }
        
        if ($listing->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
    abort(403);
}

        if ($listing->is_hidden && $listing->is_renewable == 0) {
            abort(403, 'Šis išparduotas skelbimas negali būti redaguojamas.');
        }

        $categories = Category::all();

        return view('frontend.listing-edit', compact('listing', 'categories'));
    }

    public function update(Request $request, Listing $listing)
{

    if (auth()->user()->isBannedUser() && auth()->user()->role !== 'admin') {
            return redirect()
                ->route('home')
                ->with('error', 'Jūsų paskyra apribota. Negalite redaguoti skelbimų.');
        }
    
    if ($listing->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
        abort(403);
    }

    $data = $request->validate([
        'pavadinimas'   => 'required|string|max:255',
        'aprasymas'     => 'required|string|max:2000',
        'kaina'         => 'required|numeric|min:0.20|max:99999',
        'tipas'         => 'required|in:preke,paslauga',
        'category_id'   => 'required|exists:category,id',
        'kiekis'        => 'required_if:tipas,preke|integer|min:1|max:999',
        'package_size'  => 'required_if:tipas,preke|in:XS,S,M,L',
        'is_renewable'  => 'nullable|boolean',
        'photos.*'      => 'nullable|image|max:4096',
    ]);

    $data['is_renewable'] = $request->has('is_renewable') ? 1 : 0;

    $this->listingService->update($listing->id, $data);

    if ($request->hasFile('photos')) {
        foreach ($request->photos as $photo) {
            $path = $photo->store('listing_photos', 'photos');

            ListingPhoto::create([
                'listing_id' => $listing->id,
                'failo_url'  => $path,
            ]);
        }
    }

    if (auth()->user()->role === 'admin') {
        return redirect()
            ->route('admin.reported-listings.show', $listing->id)
            ->with('success', 'Skelbimas sėkmingai atnaujintas!');
    }

    return redirect()
        ->route('listing.single', $listing->id)
        ->with('success', 'Skelbimas sėkmingai atnaujintas!');
}

   public function deletePhoto(Listing $listing, ListingPhoto $photo)
{
     if (auth()->user()->isBannedUser()) {
            return redirect()
                ->route('home')
                ->with('error', 'Jūsų paskyra apribota. Negalite valdyti skelbimų.');
        }
    
    if ($listing->user_id !== auth()->id()) {
        abort(403);
    }

    if ($photo->listing_id !== $listing->id) {
        abort(404);
    }

    if ($listing->photos()->count() <= 1) {
        return back()->with('error', 'Skelbimas privalo turėti bent vieną nuotrauką.');
    }

    Storage::disk('photos')->delete($photo->failo_url);
    $photo->delete();

    return back()->with('success', 'Nuotrauka sėkmingai ištrinta.');
}

}
