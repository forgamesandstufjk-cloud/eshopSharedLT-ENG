<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewReport extends Model
{
    use HasFactory;

    protected $table = 'review_reports';

    protected $fillable = [
        'review_id',
        'reported_user_id',
        'reporter_user_id',
        'reason',
        'details',
        'status',
        'reviewed_by_admin_id',
        'reviewed_at',
        'admin_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reporterUser()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reviewedByAdmin()
    {
        return $this->belongsTo(User::class, 'reviewed_by_admin_id');
    }
}