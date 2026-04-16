<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    use HasFactory;
    
    protected $table = 'listing_reports';
    
    protected $fillable = [
        'reported_user_id',
        'reporter_user_id',
        'listing_id',
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

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reporterUser()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function reviewedByAdmin()
    {
        return $this->belongsTo(User::class, 'reviewed_by_admin_id');
    }
}
