<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['listing_id', 'failo_url'];

    public function listing() 
    {
        return $this->belongsTo(Listing::class);
    }

    protected $appends = ['url'];

public function getUrlAttribute()
{
    return \Illuminate\Support\Facades\Storage::disk('photos')->url($this->failo_url);
}
}
