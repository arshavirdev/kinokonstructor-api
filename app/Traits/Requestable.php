<?php

namespace App\Traits;

use App\Models\Request;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Requestable
{
    public function requests(): MorphMany
    {
        return $this->morphMany(Request::class, 'requestable');
    }
}