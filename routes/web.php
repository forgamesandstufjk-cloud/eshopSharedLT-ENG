<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;  
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Frontend\HomeSearchController; 
use App\Http\Controllers\Frontend\MyListingsController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\ListingCreateController;
use App\Http\Controllers\Frontend\ReviewController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\StripeConnectController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Models\OrderShipment;
use App\Http\Controllers\Frontend\ListingController;
use App\Http\Controllers\Frontend\SellerOrderController;
use App\Http\Controllers\Frontend\BuyerOrderController;
use App\Http\Controllers\Admin\ShipmentModerationController;
use App\Models\User;
use App\Http\Controllers\Admin\AdminReportedListingController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\ReviewReportController;
use App\Http\Controllers\Frontend\SellerServiceOrderController;
use App\Models\ServiceOrder;
use Illuminate\Support\Str;


Route::get('/generate-buyer-codes', function () {
    $users = User::whereNull('buyer_code')->get();

    $updated = 0;

    foreach ($users as $user) {
        do {
            $code = strtoupper(Str::random(6));
        } while (User::where('buyer_code', $code)->exists());

        $user->buyer_code = $code;
        $user->save();
        $updated++;
    }

    return "Buyer codes generated for {$updated} users.";
}); 

Route::middleware('auth')->get('/service-order-proof/{serviceOrder}', function (ServiceOrder $serviceOrder) {
    if ((int) $serviceOrder->seller_id !== (int) auth()->id() && auth()->user()->role !== 'admin') {
        abort(403); 
    }

    if (!$serviceOrder->proof_path || !Storage::disk('public')->exists($serviceOrder->proof_path)) {
        abort(404);
    }

    return response()->file(Storage::disk('public')->path($serviceOrder->proof_path));
})->name('service-orders.proof');

Route::middleware(['auth'])->get('/_debug/checkout-address', function () {
    $u = auth()->user()->load('Address.city.country');

    return response()->json([
        'user_id' => $u->id,
        'address_id' => $u->address_id,
        'address_exists' => (bool) $u->Address,
        'address' => [
            'id' => $u->Address?->id,
            'city_id' => $u->Address?->city_id ?? null,
            'street' => $u->Address->gatve ?? null,
            'house_number' => $u->Address->namo_numeris ?? null,
            'postal_code' => $u->Address->pasto_kodas ?? null,
        ],
        'city' => [
            'id' => $u->Address?->city?->id,
            'name' => $u->Address?->city?->pavadinimas,
        ],
        'country' => [
            'id' => $u->Address?->city?->country?->id,
            'name' => $u->Address?->city?->country?->pavadinimas,
        ],
    ]);
});

Route::middleware(['auth'])->get('/_debug/cart-checkout', function () {
    $cartItems = \App\Models\Cart::with([
        'listing.user',
        'listing.category',
        'listing.photos',
    ])
    ->where('user_id', auth()->id())
    ->get();

    return response()->json([
        'user_id' => auth()->id(),
        'cart_count' => $cartItems->count(),
        'items' => $cartItems->map(function ($item) {
            return [
                'cart_id' => $item->id,
                'kiekis' => $item->kiekis,
                'listing_exists' => (bool) $item->listing,
                'listing_id' => $item->listing?->id,
                'listing_title' => $item->listing?->pavadinimas,
                'listing_hidden' => $item->listing?->is_hidden,
                'listing_statusas' => $item->listing?->statusas,
                'listing_package_size' => $item->listing?->package_size,
                'listing_price' => $item->listing?->kaina,
                'seller_id' => $item->listing?->user?->id,
                'seller_name' => $item->listing?->user?->vardas,
                'seller_email' => $item->listing?->user?->el_pastas,
                'seller_banned' => $item->listing?->user?->is_banned,
                'seller_stripe_account_id' => $item->listing?->user?->stripe_account_id,
                'seller_stripe_onboarded' => $item->listing?->user?->stripe_onboarded,
            ];
        }),
        'buyer' => [
            'id' => auth()->user()->id,
            'address_id' => auth()->user()->address_id,
        ],
    ]);
});

Route::middleware(['auth', 'admin'])->get('/_admin/cleanup-stale-listing-links', function () {
    $deletedCartRows = \App\Models\Cart::where(function ($q) {
        $q->whereDoesntHave('listing')
          ->orWhereHas('listing', function ($q2) {
              $q2->where('is_hidden', true)
                 ->orWhere('statusas', 'parduotas');
          });
    })->delete();

    $deletedFavoriteRows = \App\Models\Favorite::where(function ($q) {
        $q->whereDoesntHave('listing')
          ->orWhereHas('listing', function ($q2) {
              $q2->where('is_hidden', true)
                 ->orWhere('statusas', 'parduotas');
          });
    })->delete();

    return response()->json([
        'ok' => true,
        'deleted_cart_rows' => $deletedCartRows,
        'deleted_favorite_rows' => $deletedFavoriteRows,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::post('/review/{review}/report', [ReviewReportController::class, 'store'])
        ->name('review.report');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reported-listings', [AdminReportedListingController::class, 'index'])
        ->name('reported-listings.index');

    Route::get('/shipments', [ShipmentModerationController::class, 'index'])
    ->name('shipments.index');

Route::post('/shipments/{shipment}/approve', [ShipmentModerationController::class, 'approve'])
    ->name('shipments.approve');

Route::post('/shipments/{shipment}/reject', [ShipmentModerationController::class, 'reject'])
    ->name('shipments.reject');

Route::post('/service-shipments/{serviceOrder}/approve', [ShipmentModerationController::class, 'approveService'])
    ->name('service-shipments.approve');

Route::post('/service-shipments/{serviceOrder}/reject', [ShipmentModerationController::class, 'rejectService'])
    ->name('service-shipments.reject');

    Route::get('/reported-listings/{listing}', [AdminReportedListingController::class, 'show'])
        ->name('reported-listings.show');

    Route::post('/reported-listings/{listing}/dismiss-reason', [AdminReportedListingController::class, 'dismissReason'])
        ->name('reported-listings.dismiss-reason');

    Route::post('/reported-listings/{listing}/remove', [AdminReportedListingController::class, 'remove'])
        ->name('reported-listings.remove');

    Route::post('/reported-listings/{listing}/ban-seller', [AdminReportedListingController::class, 'banSeller'])
        ->name('reported-listings.ban-seller');

    Route::post('/reported-users/{user}/unban', [AdminReportedListingController::class, 'unbanSeller'])
        ->name('reported-listings.unban-seller');

        Route::post('/reported-users/{user}/ban', [AdminReportedListingController::class, 'banReporter'])
            ->name('reported-listings.ban-reporter');

    Route::get('/reported-users/{user}/listings', [AdminReportedListingController::class, 'userListings'])
        ->name('reported-listings.user-listings');

    Route::get('/reported-users/{user}/comments', [AdminReportedListingController::class, 'userComments'])
        ->name('reported-listings.user-comments');

Route::get('/reported-users/{user}/comments/{review}', [AdminReportedListingController::class, 'compareUserComment'])
    ->name('reported-listings.compare-user-comment');

Route::delete('/reported-users/{user}/comments/{review}', [AdminReportedListingController::class, 'deleteUserComment'])
    ->name('reported-listings.delete-user-comment');

    Route::get('/reported-users/{user}/submitted-reports', [AdminReportedListingController::class, 'reporterReports'])
    ->name('reported-listings.reporter-reports');

    Route::get('/reported-comments', [AdminReportedListingController::class, 'reportedComments'])
    ->name('reported-listings.reported-comments');

Route::get('/reported-users/{user}/comments', [AdminReportedListingController::class, 'userComments'])
    ->name('reported-listings.user-comments');
});

Route::middleware('auth')->group(function () {
    Route::post('/listing/{listing}/report-user', [UserReportController::class, 'store'])
        ->name('reports.store');
});


Route::middleware('auth')->group(function () {
    Route::get('/_debug/reports', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $reports = \App\Models\UserReport::with([
            'reportedUser',
            'reporterUser',
            'listing',
            'reviewedByAdmin',
        ])
        ->latest()
        ->get()
        ->map(function ($report) {
            return [
                'report_id' => $report->id,

                'report_status' => $report->status, 
                'report_reason' => $report->reason,
                'report_details' => $report->details,
                'report_admin_note' => $report->admin_note,
                'report_reviewed_at' => $report->reviewed_at,
                'report_created_at' => $report->created_at,
                'report_updated_at' => $report->updated_at,

                'reported_user' => [
                    'id' => $report->reportedUser?->id,
                    'vardas' => $report->reportedUser?->vardas,
                    'pavarde' => $report->reportedUser?->pavarde,
                    'el_pastas' => $report->reportedUser?->el_pastas,
                    'is_banned' => (bool) ($report->reportedUser?->is_banned ?? false),
                    'ban_reason' => $report->reportedUser?->ban_reason,
                    'banned_at' => $report->reportedUser?->banned_at,
                ],

                'reporter_user' => [
                    'id' => $report->reporterUser?->id,
                    'vardas' => $report->reporterUser?->vardas,
                    'pavarde' => $report->reporterUser?->pavarde,
                    'el_pastas' => $report->reporterUser?->el_pastas,
                ],

                'listing' => [
                    'id' => $report->listing?->id,
                    'pavadinimas' => $report->listing?->pavadinimas,
                    'user_id' => $report->listing?->user_id,
                ],

                'reviewed_by_admin' => [
                    'id' => $report->reviewedByAdmin?->id,
                    'vardas' => $report->reviewedByAdmin?->vardas,
                    'pavarde' => $report->reviewedByAdmin?->pavarde,
                    'el_pastas' => $report->reviewedByAdmin?->el_pastas,
                ],
            ];
        });

        return response()->json([
            'count' => $reports->count(),
            'reports' => $reports,
        ]);
    })->name('debug.reports');
});


Route::get('/make-admin', function () {

    $userId = 3;

    $user = User::findOrFail($userId);
    $user->role = 'admin';
    $user->save();

    return "User {$user->id} is now ADMIN. ";
});     


Route::middleware('auth')->group(function () {
    Route::get('/_debug/admin-shipments', function () {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $shipments = \App\Models\OrderShipment::with([
            'order.user',
            'seller',
            'order.orderItem.listing',
        ])
        ->where('status', 'needs_review')
        ->latest()
        ->get()
        ->map(function ($s) {
            return [
                'id' => $s->id,
                'order_id' => $s->order_id,
                'seller_id' => $s->seller_id,
                'seller_name' => $s->seller->name ?? $s->seller->vardas ?? null,
                'buyer_name' => $s->order->user->name ?? $s->order->user->vardas ?? null,
                'carrier' => $s->carrier,
                'package_size' => $s->package_size,
                'shipping_cents' => $s->shipping_cents,
                'shipping_eur' => number_format(($s->shipping_cents ?? 0) / 100, 2, '.', ''),
                'tracking_number' => $s->tracking_number,
                'proof_path' => $s->proof_path,
                'status' => $s->status,
                'created_at' => $s->created_at,
                'updated_at' => $s->updated_at,
            ];
        });

        return response()->json([
            'count' => $shipments->count(),
            'shipments' => $shipments,
        ]);
    })->name('debug.admin.shipments');
});

Route::middleware('auth')->group(function () {
    Route::get('/_debug/my-shipments', function () {
        $shipments = \App\Models\OrderShipment::with([
            'order.user',
            'order.orderItem.listing',
        ])
        ->where('seller_id', auth()->id())
        ->latest()
        ->get()
        ->map(function ($s) {
            return [
                'id' => $s->id,
                'order_id' => $s->order_id,
                'seller_id' => $s->seller_id,
                'carrier' => $s->carrier,
                'package_size' => $s->package_size,
                'shipping_cents' => $s->shipping_cents,
                'shipping_eur' => number_format(($s->shipping_cents ?? 0) / 100, 2, '.', ''),
                'status' => $s->status,
                'tracking_number' => $s->tracking_number,
                'proof_path' => $s->proof_path,
                'created_at' => $s->created_at,
                'updated_at' => $s->updated_at,
            ];
        });

        return response()->json([
            'count' => $shipments->count(),
            'shipments' => $shipments,
        ]);
    })->name('debug.my.shipments');
});


Route::middleware('auth')->group(function () {
    Route::get('/_debug/me', function () {
        $u = auth()->user();

        return response()->json([
            'id' => $u->id,
            'vardas' => $u->vardas,
            'el_pastas' => $u->el_pastas,
            'role' => $u->role,
            'buyer_code' => $u->buyer_code ?? null,
            'address_id' => $u->address_id,
            'has_address' => (bool) $u->address_id,
            'stripe_account_id' => $u->stripe_account_id ?? null,
            'stripe_onboarded' => (bool) ($u->stripe_onboarded ?? false),
            'created_at' => $u->created_at,
            'updated_at' => $u->updated_at,
            'slaptazodis' => $u->slaptazodis,
        ]);
    })->name('debug.me');

    Route::post('/_debug/me/make-seller', function () {
        $u = auth()->user();

        $u->update([
            'role' => 'seller',
        ]);

        return response()->json([
            'ok' => true,
            'id' => $u->id,
            'role' => $u->role,
        ]);
    })->name('debug.makeSeller');

    Route::post('/_debug/me/make-buyer', function () {
        $u = auth()->user();

        $u->update([
            'role' => 'buyer',
        ]);

        return response()->json([
            'ok' => true,
            'id' => $u->id,
            'role' => $u->role,
        ]);
    })->name('debug.makeBuyer');
});


Route::get('/session/ping', function () {
    return response()->noContent();
})->middleware('auth');


Route::middleware('auth')->group(function () {
    Route::delete('/listing/{listing}', [ListingController::class, 'destroy'])
        ->name('listing.destroy');
});

Route::get('/my/purchases', [BuyerOrderController::class, 'index'])
    ->middleware(['auth', 'no_admin'])
    ->name('buyer.orders');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::post('/checkout/shipment', [\App\Http\Controllers\Frontend\CheckoutController::class, 'shipment'])
    ->middleware(['auth', 'no_admin'])
    ->name('checkout.shipment');

Route::post('/checkout/shipping', [\App\Http\Controllers\Frontend\CheckoutController::class, 'shipping'])
    ->middleware(['auth', 'no_admin'])
    ->name('checkout.shipping');

Route::post('/checkout/shipping/preview',
    [CheckoutController::class, 'previewShipping']
)->middleware(['auth', 'no_admin']);

Route::post('/checkout/intent', [CheckoutController::class, 'intent'])
    ->middleware(['auth', 'no_admin'])
    ->name('checkout.intent');

Route::get('/seller/stripe/dashboard', [
    \App\Http\Controllers\Frontend\StripeConnectController::class,
    'dashboard'
])->middleware(['auth', 'no_admin'])->name('stripe.dashboard');

Route::get('/media/{filename}', function ($filename) {
    $filename = basename($filename);

    $path = "listing_photos/{$filename}";

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return response()->file(
        Storage::disk('public')->path($path),
        ['Cache-Control' => 'public, max-age=86400']
    );
})->name('media.show');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeSearchController::class, 'search'])->name('search.listings');

Route::middleware(['auth', 'no_admin'])->get('/favorites', fn () => view('frontend.favorites'))
    ->name('favorites.page');

Route::middleware('auth')->group(function () {

    Route::delete('/listing/{listing}/photo/{photo}', 
        [ListingCreateController::class, 'deletePhoto'])
        ->name('listing.photo.delete');

      Route::get('/seller/stripe/connect', [StripeConnectController::class, 'connect'])
        ->name('stripe.connect');

    Route::get('/seller/stripe/refresh', [StripeConnectController::class, 'refresh'])
        ->name('stripe.refresh');

    Route::get('/seller/stripe/return', [StripeConnectController::class, 'return'])
        ->name('stripe.return');

    Route::middleware('no_admin')->group(function () {

    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');

    Route::post('/cart/add/{listing}', [CartController::class, 'add'])
        ->name('cart.add');

    Route::post('/cart/increase/{cart}', [CartController::class, 'increase'])
        ->name('cart.increase');

    Route::post('/cart/decrease/{cart}', [CartController::class, 'decrease'])
        ->name('cart.decrease');

    Route::delete('/cart/remove/{cart}', [CartController::class, 'remove'])
        ->name('cart.remove');

    Route::delete('/cart/clear', [CartController::class, 'clearAll'])
        ->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'index'])
        ->name('checkout.index');

    Route::get('/checkout/success', [CheckoutController::class, 'success'])
        ->name('checkout.success');
});

    Route::post('/listing/{listing}/review', [ReviewController::class, 'store'])
        ->name('review.store');

 Route::get('/listing/{listing}/edit', [ListingCreateController::class, 'edit'])
        ->name('listing.edit');

    Route::put('/listing/{listing}', [ListingCreateController::class, 'update'])
        ->name('listing.update');
    

    Route::middleware('seller')->group(function () {
    Route::get('/listing/create', [ListingCreateController::class, 'create'])
        ->name('listing.create');

    Route::post('/listing/create', [ListingCreateController::class, 'store'])
        ->name('listing.store');

    Route::get('/my-listings', [MyListingsController::class, 'index'])
        ->name('my.listings');

    Route::get('/seller/orders', [SellerOrderController::class, 'index'])
        ->name('seller.orders');

    Route::post('/seller/shipments/{shipment}', [SellerOrderController::class, 'ship'])
        ->name('seller.shipments.update');

        Route::prefix('/seller/service-orders')->name('seller.service-orders.')->group(function () {
    Route::get('/', [SellerServiceOrderController::class, 'index'])->name('index');
    Route::get('/create', [SellerServiceOrderController::class, 'create'])->name('create');
    Route::get('/create/{listing}', [SellerServiceOrderController::class, 'createFromListing'])->name('create.from-listing');
    Route::post('/', [SellerServiceOrderController::class, 'store'])->name('store');

    Route::get('/{serviceOrder}', [SellerServiceOrderController::class, 'show'])->name('show');
    Route::get('/{serviceOrder}/edit', [SellerServiceOrderController::class, 'edit'])->name('edit');
    Route::put('/{serviceOrder}', [SellerServiceOrderController::class, 'update'])->name('update');

    Route::patch('/{serviceOrder}/status', [SellerServiceOrderController::class, 'updateStatus'])->name('status');
    Route::post('/{serviceOrder}/shipment', [SellerServiceOrderController::class, 'submitShipment'])->name('shipment.submit');
    Route::post('/{serviceOrder}/complete-private', [SellerServiceOrderController::class, 'completePrivately'])->name('complete-private');
        });
    });
    
});
    

Route::get('/listing/{listing}', [HomeController::class, 'show'])
    ->name('listing.single');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::put('/password', [ProfileController::class, 'updatePassword'])
        ->name('password.update');
});

Route::get('/verify-email', fn () => view('auth.pending-verification'))
    ->name('verify.notice');

Route::post('/verify-email/resend', [RegisteredUserController::class, 'resend'])
    ->name('verify.resend');

Route::get('/verify/{token}', [RegisteredUserController::class, 'verify'])
    ->name('verify.complete');

Route::get('/email/verify-new/{token}', [ProfileController::class, 'verifyNewEmail'])
    ->name('email.verify.new');

Route::view('/apie-mus', 'frontend.about')->name('about');

require __DIR__.'/auth.php';
