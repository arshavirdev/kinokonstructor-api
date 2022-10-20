<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends AppModel
{
    use HasFactory;

    protected $casts = [
        'tags' => 'array'
    ];

    public function owner()
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }

    public function scopeWhereHasTags($query, array $tags)
    {
        return $query->whereRaw('jsonb_exists_all(tags, (SELECT ARRAY(SELECT jsonb_array_elements_text(?))))', json_encode($tags));
    }
}
