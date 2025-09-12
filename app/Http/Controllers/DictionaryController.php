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
            'regions' => Region::withCount('regionalBranches')->orderBy('sort_order', 'ASC')->get(),
        ];
    }

    public function show($dictionary = null)
    {
        return match ($dictionary) {
            'occupations' => Occupation::all(),
            'departments' => Department::all(),
            'applicant_occupations' => Occupation::whereIn('id', [56, 92, 103, 109, 175, 176, 177])->get(),
            'regions' => Region::orderBy('sort_order', 'ASC')->get(),
            default => $this->list()
        };
    }
}
