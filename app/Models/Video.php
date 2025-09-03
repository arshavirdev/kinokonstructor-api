<?php

namespace App\Models;

use App\Traits\Favoritable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Video extends AppModel implements HasMedia
{
    use InteractsWithMedia;
    use Favoritable;

    const VIDEO_FILE = 'video_file';
    const IMAGE_FILE = 'image_file';

    protected $fillable = [
        'title',
        'description',
        'category',
        'details',
        'video_link',
        'owner_id',
    ];

    protected $casts = [
        'details' => 'array',
        'category' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at', 'desc');
    }
}
