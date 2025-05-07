<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BranchMember extends Model implements HasMedia
{
    use InteractsWithMedia;

    const string IMAGE = 'image';

    protected $fillable = [
        'name',
        'branch_id',
        'description',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGE)->singleFile(); // only one image per member
    }
}
