<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserReportController extends Controller
{
    public function store(Request $request, Listing $listing)
    {
        if (!auth()->check()) {
            abort(403);
        }

        if (auth()->id() === $listing->user_id) {
            return back()->with('error', 'Negalite pranešti apie save.');
        }

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
            'details' => 'nullable|string|max:2000',
        ]);

        $reporterId = auth()->id();
        $reportedUserId = $listing->user_id;
        $listingId = $listing->id;
        $reason = $data['reason'];

        $sameExactReportExists = UserReport::where('reporter_user_id', $reporterId)
            ->where('reported_user_id', $reportedUserId)
            ->where('listing_id', $listingId)
            ->where('reason', $reason)
            ->exists();

        if ($sameExactReportExists) {
            return back()->with('error', 'Jūs jau pateikėte tokį pranešimą apie šį skelbimą.');
        }

        UserReport::create([
            'reported_user_id' => $reportedUserId,
            'reporter_user_id' => $reporterId,
            'listing_id' => $listingId,
            'reason' => $reason,
            'details' => $data['details'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Pranešimas išsiųstas administratoriui.');
    }
}
