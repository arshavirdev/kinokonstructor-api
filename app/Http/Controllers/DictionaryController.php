<?php

namespace App\Http\Controllers;

use App\Models\Occupation;
use App\Models\Region;

class DictionaryController extends Controller
{
    public function list()
    {
        return [
            'occupations' => Occupation::all(),
            'applicant_occupations' => Occupation::whereIn('id', [56, 92, 103, 109, 175, 176, 177])->get(),
            'regions' => Region::all(),
        ];
    }

    public function show($dictionary = null)
    {
        if (!$dictionary) return $this->list();
        if ($dictionary === 'occupations') return Occupation::all();
        if ($dictionary === 'applicant_occupations') return Occupation::whereIn('id', [56, 92, 103, 109, 175, 176, 177])->get();
        if ($dictionary === 'regions') return Region::all();
        abort(404, 'Dictionary not found');
    }
}
