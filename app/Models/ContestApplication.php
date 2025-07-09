<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ContestApplication extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    const STATUS_PENDING = 'pending';
    const STATUS_DECLINED = 'declined';
    const STATUS_ACCEPTED = 'accepted';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DECLINED,
        self::STATUS_ACCEPTED
    ];

    const CONTEST_APPLICATION_IMAGES = 'contest_application_images';
    const CONTEST_APPLICATION_FILES = 'contest_application_files';

    protected $fillable = [
        'contest_id',
        'applicant_id',
        'project_id',
        'description',
        'status'
    ];


    public function applicant()
    {
        return $this->belongsTo(Profile::class, 'applicant_id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
