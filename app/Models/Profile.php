<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Profile extends AppModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const AVATAR_MEDIA = 'avatar';
    public const ATTACHMENT_MEDIA = 'attachment';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birthday' => 'date',
    ];

    protected $guarded = ['status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function education(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProfileEducation::class);
    }

    public function experience()
    {
        return $this->hasMany(ProfileExperience::class);
    }

    public function projects()
    {
        return $this->hasMany(ProfileCustomProjects::class);
    }

    public function occupation()
    {
        return $this->belongsTo(Occupation::class);
    }

    public function customProjects()
    {
        return $this->hasMany(ProfileCustomProjects::class);
    }

    public function scopeWhereFullname(Builder $query, string $search)
    {
        $pattern = trim($search);
        return $query->whereRaw("(COALESCE(lastname, '') || ' ' || COALESCE(firstname, '') || ' ' || COALESCE(middlename, '')) % ?", $pattern);
    }

    public function scopeIsActor(Builder $query)
    {
        return $query->whereIn('occupation_id', Occupation::$actorIds);
    }

    public function scopeIsSpecialist(Builder $query)
    {
        return $query->whereNotIn('occupation_id', Occupation::$actorIds);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::AVATAR_MEDIA)
            ->singleFile()
            ->withResponsiveImages()
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('thumb')
                    ->fit(Manipulations::FIT_FILL, 150, 150)
                    ->quality(75)
                    ->optimize();
            });
        //add options

        // you can define as many collections as needed
        $this->addMediaCollection(self::ATTACHMENT_MEDIA);
        //add options
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion(self::AVATAR_MEDIA)
            ->width(368)
            ->height(232)
            ->sharpen(10);
    }
}
