<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'sort_order'
    ];

    public function regionalBranches()
    {
        return $this->hasMany(RegionalBranch::class);
    }
}
