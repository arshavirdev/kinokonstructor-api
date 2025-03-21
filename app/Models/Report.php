<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    protected $fillable = ['user_id', 'name', 'email', 'text'];

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }
}
