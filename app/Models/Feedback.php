<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'email', 'message'];

    protected $table = 'feedbacks';

    public function owner()
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }
    public function feedbackable(): MorphTo
    {
        return $this->morphTo();
    }
}
