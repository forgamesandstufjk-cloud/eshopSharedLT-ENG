<?php

use App\Http\Controllers\Api\{
    CountryController, CityController, AddressController,
    CategoryController, ListingPhotoController, CartController,
    FavoriteController, OrderController, OrderItemController,
    UserController, ListingController, StripeWebhookController
};
use App\Models\City;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {

    // PUBLIC LISTING ROUTES
    Route::get('/listing', [ListingController::class, 'index']);
    Route::get('/listing/{id}', [ListingController::class, 'show']);

    Route::get('/listings/mine', [ListingController::class, 'mine']);
    Route::get('/listings/search', [ListingController::class, 'search']);

    // CART ROUTES
    Route::delete('/cart/item', [CartController::class, 'clearItem']);
    Route::delete('/cart/clear', [CartController::class, 'clearAll']);

    // ADMIN
    Route::post('/users/{id}/ban', [UserController::class, 'ban'])->middleware('admin');
    Route::post('/users/{id}/unban', [UserController::class, 'unban'])->middleware('admin');

    // CITY LOOKUP
    Route::get('/cities/by-country/{countryId}', function ($countryId) {
        return City::where('country_id', $countryId)
            ->get(['id', 'pavadinimas']);
    });

    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
    
    // AUTHENTICATED ROUTES
    Route::middleware('auth:sanctum')->group(function () {

        // FAVORITES
       Route::get('/favorites/ids', function () {
    if (!auth('sanctum')->check() && !auth()->check()) {
        return response()->json([]);
    }

    $user = auth()->user() ?? auth('sanctum')->user();

    return $user->favoriteListings()
        ->where('listing.is_hidden', 0)
        ->where('listing.statusas', '!=', 'parduotas')
        ->pluck('listing.id');
});

Route::get('/favorites/my', [FavoriteController::class, 'my']);
        Route::post('/favorite', [FavoriteController::class, 'store']);
        Route::delete('/favorite/{listingId}', [FavoriteController::class, 'destroyByListing']);

        // PROTECTED LISTING ROUTES
        Route::post('/listing', [ListingController::class, 'store']);
        Route::put('/listing/{id}', [ListingController::class, 'update']);
        Route::delete('/listing/{id}', [ListingController::class, 'destroy']);
    });

    // OTHER RESOURCES
    Route::apiResources([
        'country'     => CountryController::class,
        'city'        => CityController::class,
        'address'     => AddressController::class,
        'category'    => CategoryController::class,
        'listingPhoto'=> ListingPhotoController::class,
//        'review'      => ReviewController::class,
        'cart'        => CartController::class,
        'order'       => OrderController::class,
        'orderItem'   => OrderItemController::class,
        'users'       => UserController::class,
    ]);

});
