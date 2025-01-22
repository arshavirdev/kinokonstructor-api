<?php

namespace App\Traits\Moderation;

use Illuminate\Database\Eloquent\Model;

class Moderation extends Model
{
    public $table = 'moderations';
    public $fillable = ['status', 'comment', 'data', 'moderated_by'];
    protected $casts = [
        'data' => 'array'
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    public function moderatable()
    {
        return $this->morphTo();
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->created_by = \Auth::id();
            self::query()
                ->where('moderatable_type', $model->moderatable_type)
                ->where('moderatable_id', $model->moderatable_id)
                ->update(['is_last' => false]);
        });
    }
}
