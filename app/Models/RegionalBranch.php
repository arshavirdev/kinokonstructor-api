<?php

namespace App\Models;

use App\Traits\Contactable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class RegionalBranch extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Contactable;

    const DOCS_FILES = 'docs_files';

    protected $fillable = [
        'title',
        'owner_id',
        'description',
        'year',
        'region_id',
        'city',
        'manager',
        'address',
    ];

    protected $casts = [
        'manager' => 'array',
    ];

    protected static function booted()
    {
        static::deleting(function ($branch) {
            $branch->clearMediaCollection(RegionalBranch::DOCS_FILES);
            $branch->members()->each(fn ($m) => $m->clearMediaCollection());
            $branch->news()->each(fn ($n) => $n->clearMediaCollection());
        });
    }

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }

    public function region(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function members()
    {
        return $this->hasMany(BranchMember::class, 'branch_id');
    }

    public function news()
    {
        return $this->hasMany(BranchNews::class, 'branch_id');
    }
}
