<?php

namespace App\Models;

use App\Traits\Moderation\Moderatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    use Moderatable;

    public const AVATAR_MEDIA = 'avatar';
    public const ATTACHMENT_MEDIA = 'attachment';

    protected $perPage = 8;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birthday' => 'date',
        'regions' => 'array',
        'privacy_hide' => 'array'
    ];

    protected $guarded = ['status'];
    protected $fillable = [
        "user_id",
        "firstname",
        "lastname",
        "middlename",
        "gender",
        "city",
        "birthday",
        "phone",
        "regions",
        "regions",
        "portfolio",
        "mass_media_mentions",
        "socials_vk",
        "socials_tg",
        "socials_ok",
        'privacy_hide',
        'org',
        'entrepreneur'
    ];

    public function org(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['is_org'] ? [
                'reg_id' => $attributes['org_reg_id'],
                'name' => $attributes['org_name'],
                'position' => $attributes['org_position'],
            ] : null,
            set: fn($value) => [
                'is_org' => !is_null($value),
                'org_reg_id' => is_null($value) ? null : $value['reg_id'],
                'org_name' => is_null($value) ? null : $value['name'],
                'org_position' => is_null($value) ? null : $value['position'],
            ]
        )->withoutObjectCaching();
    }

    public function fullname(): Attribute
    {
        return Attribute::make(
            get: function ($value, $profile) {
                $parts = [$profile['lastname'], $profile['firstname'], $profile['middlename']];
                return implode(' ', array_filter($parts, fn($part) => !is_null($part)));
            },
        );
    }

    public function entrepreneur(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['is_entrepreneur'] ? [
                'reg_id' => $attributes['entrepreneur_reg_id']
            ] : null,
            set: fn($value) => [
                'is_entrepreneur' => !is_null($value),
                'entrepreneur_reg_id' => is_null($value) ? null : $value['reg_id'],
            ]
        )->withoutObjectCaching();
    }

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

    public function locations()
    {
        return $this->hasMany(Location::class, 'owner_id');
    }

    public function occupations()
    {
        return $this->belongsToMany(Occupation::class, 'profile_occupations');
    }

    public function regions()
    {
        return $this->hasMany(Region::class);
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
        return $query->whereHas('occupations', function (Builder $query) {
            $query->whereIn('id', Occupation::$actorIds);
        });
    }

    public function scopeIsSpecialist(Builder $query)
    {
        return $query->whereHas('occupations', function (Builder $query) {
            $query->whereNotIn('id', Occupation::$actorIds);
        });
    }

    public function scopeWhereHasOccupation(Builder $query, string $id)
    {
        return $query->whereHas('occupations', function (Builder $query) use ($id) {
            $query->where('id', $id);
        });
    }

    public function scopeWhereAge(Builder $query, string $age)
    {
        $is_range = str_contains($age, '-');
        $lower_age = (int)($is_range ? explode('-', $age)[0] : $age);
        $upper_age = (int)($is_range ? explode('-', $age)[1] : $age);

        $lower_date = Carbon::now()->subYears($upper_age + 1)->endOf('day')->toJSON();
        $upper_date = Carbon::now()->subYears($lower_age)->startOf('day')->toJSON();
        return $query->whereBetween('birthday', [$lower_date, $upper_date]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::AVATAR_MEDIA)
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('thumb')
                    ->performOnCollections([self::AVATAR_MEDIA])
                    ->fit(Manipulations::FIT_MAX, 100, 100)
                    ->quality(75)
                    ->optimize();
                $this
                    ->addMediaConversion('medium')
                    ->performOnCollections([self::AVATAR_MEDIA])
                    ->fit(Manipulations::FIT_MAX, 400, 400)
                    ->quality(75)
                    ->optimize();
            });

        $this->addMediaCollection(self::ATTACHMENT_MEDIA);
    }
}
