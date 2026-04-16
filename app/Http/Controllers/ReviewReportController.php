<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewReportController extends Controller
{
    public function store(Request $request, Review $review)
    {
        if (!auth()->check()) {
            abort(403);
        }

        if ($review->user_id === auth()->id()) {
            return back()->with('error', 'Negalite pranešti apie savo atsiliepimą.');
        }

        $data = $request->validate([
            'reason' => [
                'required',
                'string',
                Rule::in([
                    'abuse',
                    'spam',
                    'fake_review',
                    'harassment',
                    'other',
                ]),
            ],
            'details' => 'nullable|string|max:2000',
        ]);

        $alreadyReported = ReviewReport::where('review_id', $review->id)
            ->where('reporter_user_id', auth()->id())
            ->where('reason', $data['reason'])
            ->exists();

        if ($alreadyReported) {
            return back()->with('error', 'Jūs jau pranešėte apie šį atsiliepimą dėl šios priežasties.');
        }

        ReviewReport::create([
            'review_id' => $review->id,
            'reported_user_id' => $review->user_id,
            'reporter_user_id' => auth()->id(),
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Pranešimas apie atsiliepimą išsiųstas administratoriui.');
    }
}