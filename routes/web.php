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
use App\Mail\BuyerShipmentShippedMail;
use Illuminate\Support\Facades\Mail;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/search', [HomeSearchController::class, 'search'])
    ->name('search.listings');

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

Route::get('/listing/{listing}', [HomeController::class, 'show'])
    ->name('listing.single');

Route::get('/verify-email', fn () => view('auth.pending-verification'))
    ->name('verify.notice');

Route::post('/verify-email/resend', [RegisteredUserController::class, 'resend'])
    ->name('verify.resend');

Route::get('/verify/{token}', [RegisteredUserController::class, 'verify'])
    ->name('verify.complete');

Route::get('/email/verify-new/{token}', [ProfileController::class, 'verifyNewEmail'])
    ->name('email.verify.new');

Route::view('/apie-mus', 'frontend.about')
    ->name('about');

Route::middleware('auth')->group(function () {
    Route::get('/listing/{listing}/seller-contact', [HomeController::class, 'sellerContact'])
        ->middleware('throttle:10,1')
        ->name('listing.seller-contact');

    Route::put('/review/{review}', [ReviewController::class, 'update'])
        ->name('review.update');

    Route::post('/review/{review}/report', [ReviewReportController::class, 'store'])
        ->name('review.report');

    Route::post('/listing/{listing}/report-user', [UserReportController::class, 'store'])
        ->name('reports.store');

    Route::delete('/listing/{listing}', [ListingController::class, 'destroy'])
        ->name('listing.destroy');

    Route::delete('/listing/{listing}/photo/{photo}', [ListingCreateController::class, 'deletePhoto'])
        ->name('listing.photo.delete');

    Route::post('/listing/{listing}/review', [ReviewController::class, 'store'])
        ->name('review.store');

    Route::get('/listing/{listing}/edit', [ListingCreateController::class, 'edit'])
        ->name('listing.edit');

    Route::put('/listing/{listing}', [ListingCreateController::class, 'update'])
        ->name('listing.update');

    Route::get('/seller/stripe/connect', [StripeConnectController::class, 'connect'])
        ->name('stripe.connect');

    Route::get('/seller/stripe/refresh', [StripeConnectController::class, 'refresh'])
        ->name('stripe.refresh');

    Route::get('/seller/stripe/return', [StripeConnectController::class, 'return'])
        ->name('stripe.return');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::put('/password', [ProfileController::class, 'updatePassword'])
        ->name('password.update');

    Route::get('/session/ping', function () {
        return response()->noContent();
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/service-order-proof/{serviceOrder}', function (ServiceOrder $serviceOrder) {
        if ((int) $serviceOrder->seller_id !== (int) auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        if (!$serviceOrder->proof_path || !Storage::disk('public')->exists($serviceOrder->proof_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($serviceOrder->proof_path));
    })->name('service-orders.proof');
});

Route::middleware(['auth', 'no_admin'])->group(function () {
    Route::get('/favorites', fn () => view('frontend.favorites'))
        ->name('favorites.page');

    Route::get('/my/purchases', [BuyerOrderController::class, 'index'])
        ->name('buyer.orders');

    Route::post('/checkout/shipment', [CheckoutController::class, 'shipment'])
        ->name('checkout.shipment');

    Route::post('/checkout/shipping', [CheckoutController::class, 'shipping'])
        ->name('checkout.shipping');

    Route::post('/checkout/shipping/preview', [CheckoutController::class, 'previewShipping']);

    Route::post('/checkout/intent', [CheckoutController::class, 'intent'])
        ->name('checkout.intent');

    Route::get('/seller/stripe/dashboard', [StripeConnectController::class, 'dashboard'])
        ->name('stripe.dashboard');

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

Route::middleware(['auth', 'seller'])->group(function () {
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
        Route::get('/', [SellerServiceOrderController::class, 'index'])
            ->name('index');

        Route::get('/create', [SellerServiceOrderController::class, 'create'])
            ->name('create');

        Route::get('/create/{listing}', [SellerServiceOrderController::class, 'createFromListing'])
            ->name('create.from-listing');

        Route::post('/', [SellerServiceOrderController::class, 'store'])
            ->name('store');

        Route::get('/{serviceOrder}', [SellerServiceOrderController::class, 'show'])
            ->name('show');

        Route::get('/{serviceOrder}/edit', [SellerServiceOrderController::class, 'edit'])
            ->name('edit');

        Route::put('/{serviceOrder}', [SellerServiceOrderController::class, 'update'])
            ->name('update');

        Route::post('/{serviceOrder}/choose-platform', [SellerServiceOrderController::class, 'choosePlatform'])
            ->name('choose-platform');

        Route::post('/{serviceOrder}/choose-private', [SellerServiceOrderController::class, 'choosePrivate'])
            ->name('choose-private');

        Route::patch('/{serviceOrder}/status', [SellerServiceOrderController::class, 'updateStatus'])
            ->name('status');

        Route::post('/{serviceOrder}/shipment', [SellerServiceOrderController::class, 'submitShipment'])
            ->name('shipment.submit');

        Route::post('/{serviceOrder}/complete-private', [SellerServiceOrderController::class, 'completePrivately'])
            ->name('complete-private');
    });
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/reported-listings', [AdminReportedListingController::class, 'index'])
            ->name('reported-listings.index');

        Route::get('/reported-listings/{listing}', [AdminReportedListingController::class, 'show'])
            ->name('reported-listings.show');

        Route::post('/reported-listings/{listing}/dismiss-reason', [AdminReportedListingController::class, 'dismissReason'])
            ->name('reported-listings.dismiss-reason');

        Route::post('/reported-listings/{listing}/remove', [AdminReportedListingController::class, 'remove'])
            ->name('reported-listings.remove');

        Route::post('/reported-listings/{listing}/ban-seller', [AdminReportedListingController::class, 'banSeller'])
            ->name('reported-listings.ban-seller');

        Route::get('/reported-comments', [AdminReportedListingController::class, 'reportedComments'])
            ->name('reported-listings.reported-comments');

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
    });
});

require __DIR__ . '/auth.php';
