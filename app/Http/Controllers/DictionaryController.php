<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Occupation;
use App\Models\Region;
use App\Models\RegionalBranch;

class DictionaryController extends Controller
{
    public function list()
    {
        return [
            'occupations' => Occupation::all(),
            'departments' => Department::all(),
            'applicant_occupations' => Occupation::whereIn('id', [56, 92, 103, 109, 175, 176, 177])->get(),
            'regions' => Region::orderBy('sort_order', 'ASC')->get()
                ->map(function ($region) {
                    // TODO: optimize: use pivot table
                    $region->regional_branches_count = RegionalBranch::whereJsonContains('region_ids', $region->id)->count();
                    return $region;
                })
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
