<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminBlockedUserMail;
use App\Mail\AdminRemovedListingMail;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use App\Models\UserReport;
use App\Services\ListingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Mail\AdminRemovedReviewMail;

class AdminReportedListingController extends Controller
{
    public function index()
    {
        $listingIds = UserReport::where('status', 'pending')
            ->whereNotNull('listing_id')
            ->distinct()
            ->pluck('listing_id');

        $listings = Listing::with(['photos', 'user'])
            ->whereIn('id', $listingIds)
            ->where('is_hidden', 0)
            ->get()
            ->map(function ($listing) {
                $listing->reports_count = UserReport::where('listing_id', $listing->id)
                    ->where('status', 'pending')
                    ->count();

                return $listing;
            })
            ->sortByDesc('reports_count')
            ->values();

        return view('admin.reports.index', compact('listings'));
    }

    public function show(Listing $listing)
    {
        $listing->load(['photos', 'user', 'category', 'review.user']);

        $similar = Listing::where('user_id', $listing->user_id)
            ->where('id', '!=', $listing->id)
            ->where('is_hidden', 0)
            ->where('statusas', '!=', 'parduotas')
            ->with('photos')
            ->take(4)
            ->get();

        $seller = $listing->user;

        $listingReportsCount = UserReport::where('reported_user_id', $seller->id)->count();

        $activeReviewsCount = Review::where('user_id', $seller->id)
        ->whereNotNull('komentaras')
        ->where('komentaras', '!=', '')
        ->count();

        $removedReviewsCount = (int) ($seller->removed_reviews_count ?? 0);

        $commentReportsCount = $activeReviewsCount + $removedReviewsCount;

        $reportsByReason = UserReport::with(['reporterUser', 'reviewedByAdmin'])
            ->where('listing_id', $listing->id)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('reason');

        return view('admin.reports.show', compact(
            'listing',
            'similar',
            'seller',
            'listingReportsCount',
            'commentReportsCount',
            'reportsByReason'
        ));
    }

    public function dismissReason(Request $request, Listing $listing)
    {
        $data = $request->validate([
            'reason' => [
                'required',
                'string',
                Rule::in([
                    'fraud',
                    'fake_item',
                    'abuse',
                    'spam',
                    'prohibited_items',
                    'other',
                ]),
            ],
            'admin_note' => 'nullable|string|max:2000',
        ]);

        UserReport::where('listing_id', $listing->id)
            ->where('reason', $data['reason'])
            ->where('status', 'pending')
            ->update([
                'status' => 'dismissed',
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_by_admin_id' => auth()->id(),
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Šios priežasties pranešimai atmesti.');
    }

 public function remove(Request $request, Listing $listing, ListingService $listingService)
{
    $data = $request->validate([
        'removal_reason' => [
            'required',
            'string',
            Rule::in([
                'fraud',
                'fake_item',
                'abuse',
                'spam',
                'prohibited_items',
                'other',
            ]),
        ],
        'admin_note' => 'nullable|string|max:2000|required_if:removal_reason,other',
    ]);

    $reasonLabel = match ((string) $data['removal_reason']) {
        'fraud' => 'Sukčiavimas',
        'fake_item' => 'Netikra prekė',
        'abuse' => 'Įžeidžiantis elgesys',
        'spam' => 'Šlamštas',
        'prohibited_items' => 'Draudžiamos prekės',
        'other' => 'Kita',
        default => (string) $data['removal_reason'],
    };

    $resolvedRemovalReason = (string) (
        $data['removal_reason'] === 'other'
            ? ($data['admin_note'] ?: 'Kita administratoriaus nurodyta priežastis')
            : $reasonLabel
    );

    $historyAdminNote = 'Skelbimas pašalintas administratoriaus. Priežastis: ' . $resolvedRemovalReason;

    $listing->loadMissing('user');

    $seller = $listing->user;
    $sellerEmail = $seller?->el_pastas;
    $sellerName = trim(($seller?->vardas ?? '') . ' ' . ($seller?->pavarde ?? ''));
    $listingTitle = (string) $listing->pavadinimas;

    if ($sellerEmail) {
        Mail::to($sellerEmail)->send(
            new \App\Mail\AdminRemovedListingMail(
                $sellerName,
                $listingTitle,
                $resolvedRemovalReason,
                $data['admin_note'] ?? null
            )
        );
    }

    $result = $listingService->delete($listing);

    UserReport::where('listing_id', $listing->id)
        ->where('status', 'pending')
        ->update([
            'status' => 'resolved',
            'admin_note' => $historyAdminNote,
            'reviewed_by_admin_id' => auth()->id(),
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);

    return redirect()
        ->route('admin.reported-listings.index')
        ->with(
            'success',
            $result === 'hidden'
                ? 'Skelbimas turi pardavimų istoriją, todėl buvo paslėptas ir pranešimai išspręsti.'
                : 'Skelbimas pašalintas ir pranešimai išspręsti.'
        );
}
    
public function banSeller(Request $request, Listing $listing, ListingService $listingService)
{
    $data = $request->validate([
        'admin_note' => 'required|string|max:2000',
    ]);

    $seller = $listing->user;

    $seller->update([
        'is_banned' => true,
        'ban_reason' => $data['admin_note'],
        'banned_at' => now(),
    ]);

    $sellerListings = Listing::where('user_id', $seller->id)->get();

    foreach ($sellerListings as $sellerListing) {
        \App\Models\Cart::where('listing_id', $sellerListing->id)->delete();
        \App\Models\Favorite::where('listing_id', $sellerListing->id)->delete();

        $listingService->delete($sellerListing);
    }

    UserReport::where('reported_user_id', $seller->id)
        ->where('status', 'pending')
        ->update([
            'status' => 'resolved',
            'admin_note' => $data['admin_note'],
            'reviewed_by_admin_id' => auth()->id(),
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);

    try {
        if ($seller->el_pastas) {
            \Illuminate\Support\Facades\Mail::to($seller->el_pastas)->queue(
                new \App\Mail\AdminBlockedUserMail($seller, $data['admin_note'])
            );
        }
    } catch (\Throwable $e) {
        report($e);
    }

    return redirect()
        ->route('admin.reported-listings.index')
        ->with('success', 'Naudotojas užblokuotas, jo skelbimai apdoroti, krepšeliai ir mėgstamiausi išvalyti.');
}

        public function unbanSeller(User $user)
{
    $user->update([
        'is_banned' => false,
        'ban_reason' => null,
        'banned_at' => null,
    ]);

    return back()->with('success', 'Naudotojas atblokuotas.');
}
    
    
    public function userListings(User $user)
{
    $listings = Listing::with('photos')
        ->where('user_id', $user->id)
        ->where('is_hidden', 0)
        ->latest()
        ->paginate(24);

    $listings->getCollection()->transform(function ($listing) {
        $listing->reports_count = UserReport::where('listing_id', $listing->id)->count();
        return $listing;
    });

    return view('admin.reports.user-listings', compact('user', 'listings'));
}


public function compareUserComment(User $user, \App\Models\Review $review)
{
    if ($review->user_id !== $user->id) {
        abort(404);
    }

    $review->load(['user', 'Listing.photos', 'Listing.user', 'Listing.category']);

    $listing = $review->Listing;
    $seller = $listing->user;

    $listingReportsCount = \App\Models\UserReport::where('reported_user_id', $seller->id)->count();

    $activeReviewsCount = \App\Models\Review::where('user_id', $seller->id)
        ->whereNotNull('komentaras')
        ->where('komentaras', '!=', '')
        ->count();

    $removedReviewsCount = (int) ($seller->removed_reviews_count ?? 0);

    $commentReportsCount = $activeReviewsCount + $removedReviewsCount;

    $listingReviews = \App\Models\Review::with(['user'])
        ->where('listing_id', $review->listing_id)
        ->whereNotNull('komentaras')
        ->where('komentaras', '!=', '')
        ->orderByRaw('id = ? desc', [$review->id])
        ->latest()
        ->get();

    return view('admin.reports.review-comparison', [
        'user' => $user,
        'selectedReview' => $review,
        'listing' => $listing,
        'seller' => $seller,
        'listingReportsCount' => $listingReportsCount,
        'commentReportsCount' => $commentReportsCount,
        'listingReviews' => $listingReviews,
    ]);
}

public function deleteUserComment(User $user, \App\Models\Review $review, Request $request)
{
    if ($review->user_id !== $user->id) {
        abort(404);
    }

    $data = $request->validate([
        'admin_note' => 'required|string|max:2000',
    ]);

    $review->load(['user', 'Listing']);

    try {
        if ($review->user?->el_pastas) {
            Mail::to($review->user->el_pastas)->queue(
                new AdminRemovedReviewMail($review, $data['admin_note'])
            );
        }
    } catch (\Throwable $e) {
        report($e);
    }

    $review->user->increment('removed_reviews_count');

    $review->delete();

    return redirect()
        ->route('admin.reported-listings.user-comments', $user)
        ->with('success', 'Komentaras pašalintas.');
}

public function reporterReports(User $user)
{
    $reports = UserReport::with(['listing.photos', 'reportedUser'])
        ->where('reporter_user_id', $user->id)
        ->latest()
        ->paginate(20);

    $stats = [
        'total' => UserReport::where('reporter_user_id', $user->id)->count(),
        'dismissed' => UserReport::where('reporter_user_id', $user->id)->where('status', 'dismissed')->count(),
        'resolved' => UserReport::where('reporter_user_id', $user->id)->where('status', 'resolved')->count(),
        'pending' => UserReport::where('reporter_user_id', $user->id)->where('status', 'pending')->count(),
    ];

    return view('admin.reports.reporter-reports', [
        'user' => $user,
        'reports' => $reports,
        'stats' => $stats,
    ]);
}

public function reportedComments()
{
    $reviews = \App\Models\Review::with(['Listing.photos', 'user'])
        ->whereHas('reports', function ($q) {
            $q->where('status', 'pending');
        })
        ->latest()
        ->paginate(20);

    $reviews->getCollection()->transform(function ($review) {
        $review->pending_reports_count = $review->reports()
            ->where('status', 'pending')
            ->count();

        return $review;
    });

    return view('admin.reports.user-comments', [
        'user' => null,
        'reviews' => $reviews,
        'mode' => 'reported_only',
    ]);
}

public function userComments(User $user)
{
    $reviews = \App\Models\Review::with(['Listing.photos', 'user'])
        ->where('user_id', $user->id)
        ->whereNotNull('komentaras')
        ->where('komentaras', '!=', '')
        ->latest()
        ->paginate(20);

    $reviews->getCollection()->transform(function ($review) {
        $review->pending_reports_count = $review->reports()
            ->where('status', 'pending')
            ->count();

        return $review;
    });

    return view('admin.reports.user-comments', [
        'user' => $user,
        'reviews' => $reviews,
        'mode' => 'user_scope',
    ]);
}

public function banReporter(Request $request, User $user)
{
    $data = $request->validate([
        'admin_note' => 'required|string|max:2000',
    ]);

    $user->update([
        'is_banned' => true,
        'ban_reason' => $data['admin_note'],
        'banned_at' => now(),
    ]);

    try {
        if ($user->el_pastas) {
            \Illuminate\Support\Facades\Mail::to($user->el_pastas)->queue(
                new \App\Mail\AdminBlockedUserMail($user, $data['admin_note'])
            );
        }
    } catch (\Throwable $e) {
        report($e);
    }

    return back()->with('success', 'Naudotojas užblokuotas.');
}

}
