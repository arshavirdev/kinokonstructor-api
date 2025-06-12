<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Summary of Department
 * @property number $id
 * @property string $label
 */
class Department extends Model
{
    protected $fillable = [
        'label'
    ];
}
