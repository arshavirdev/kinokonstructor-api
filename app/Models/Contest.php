<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\Favoritable;
use App\Traits\Moderation\Moderatable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Contest extends AppModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use Moderatable;
    use Favoritable;

    public const GALLERY = 'gallery';
    public const DOCUMENTS = 'documents';
    public const LOGO = 'logo';
    public const PHOTO_GALLERY = 'photo_gallery';
    public const PARTNERS = 'partners';

    public $timestamps = false;

    protected $fillable = [
        'owner_id',
        'title',
        'type',
        'years_held',
        'country',
        'region',
        'city',
        'description',
        'conditions',
        'deadlines',
        'prizes',
        'jury',
        'organizers',
        'online_application',  
        'video_link',
        'is_archived'
    ];

    protected $casts = [
        'deadlines' => 'array',
        'is_archived' => 'boolean'
    ];

    public function owner()
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }

    public function contacts(): HasOne
    {
        return $this->hasOne(ContestContact::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::GALLERY)
        ->registerMediaConversions(function (Media $media) {
            $this->addMediaConversion('large')
                ->fit(Manipulations::FIT_MAX, 1024, 1024)
                ->quality(75)
                ->optimize();
            $this->addMediaConversion('thumb')
                ->fit(Manipulations::FIT_MAX, 150, 150)
                ->quality(70)
                ->optimize();
            $this->addMediaConversion('preview')
                ->fit(Manipulations::FIT_MAX, 350, 350)
                ->quality(75)
                ->optimize();
        });

        // Documents Collection
        $this->addMediaCollection(self::DOCUMENTS);

        // Logo Collection (Single File)
        $this->addMediaCollection(self::LOGO)->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->fit(Manipulations::FIT_MAX, 100, 100)
                    ->quality(75)
                    ->optimize();
                $this->addMediaConversion('medium')
                    ->fit(Manipulations::FIT_MAX, 400, 400)
                    ->quality(75)
                    ->optimize();
            });

        // Photo Gallery Collection
        $this->addMediaCollection(self::PHOTO_GALLERY)
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('large')
                    ->fit(Manipulations::FIT_MAX, 1024, 1024)
                    ->quality(75)
                    ->optimize();
                $this->addMediaConversion('thumb')
                    ->fit(Manipulations::FIT_MAX, 150, 150)
                    ->quality(70)
                    ->optimize();
            });

        // Partners Collection
        $this->addMediaCollection(self::PARTNERS);
    }
}
