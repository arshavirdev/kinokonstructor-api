<?php

namespace App\Traits;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Contactable
{
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'contactable');
    }
}