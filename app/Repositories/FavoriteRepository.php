<?php

namespace App\Repositories;

use App\Models\Favorite;
use App\Repositories\Contracts\FavoriteRepositoryInterface;
use Illuminate\Support\Collection;

class FavoriteRepository extends BaseRepository implements FavoriteRepositoryInterface
{
    public function __construct(Favorite $model)
    {
        parent::__construct($model);
    }
    
    public function getAll(): Collection
    {
        Favorite::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->whereDoesntHave('listing')
                  ->orWhereHas('listing', function ($q2) {
                      $q2->where('is_hidden', true)
                         ->orWhere('statusas', 'parduotas');
                  });
            })
            ->delete();

        return Favorite::where('user_id', auth()->id())
            ->whereHas('listing', function ($q) {
                $q->where('is_hidden', false)
                  ->where('statusas', '!=', 'parduotas');
            })
            ->with([
                'listing.photos',
                'listing.category',
                'listing.user',
            ])
            ->get();
    }

    public function getById(int $id): ?Favorite
    {
        return Favorite::where('user_id', auth()->id())
            ->whereHas('listing', function ($q) {
                $q->where('is_hidden', false)
                  ->where('statusas', '!=', 'parduotas');
            })
            ->with([
                'listing.photos',
                'listing.category',
                'listing.user',
            ])
            ->find($id);
    }
}
