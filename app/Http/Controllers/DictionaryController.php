<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Occupation;
use App\Models\Region;

class DictionaryController extends Controller
{
    public function list()
    {
        return [
            'occupations' => Occupation::all(),
            'departments' => Department::all(),
            'applicant_occupations' => Occupation::whereIn('id', [56, 92, 103, 109, 175, 176, 177])->get(),
            'regions' => Region::all(),
        ];
    }

    public function show($dictionary = null)
    {
        return match ($dictionary) {
            'occupations' => Occupation::all(),
            'departments' => Department::all(),
            'applicant_occupations' => Occupation::whereIn('id', [56, 92, 103, 109, 175, 176, 177])->get(),
            'regions' => Region::all(),
            default => $this->list()
        };
    }
}
