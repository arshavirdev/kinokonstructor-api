<?php

namespace App\Traits;

use App\Models\Favorite;
use Auth;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Favoritable
{
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function isFavoritedBy($user): bool
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function getIsFavoritedAttribute(): bool
    {
        return $this->favorites()->where('user_id', Auth::id())->exists();
    }
}
