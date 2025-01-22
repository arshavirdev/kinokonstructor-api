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
            'regions' => Region::all(),
        ];
    }

    public function show($dictionary = null)
    {
        if (!$dictionary) return $this->list();
        if ($dictionary === 'occupations') return Occupation::all();
        if ($dictionary === 'regions') return Region::all();
        abort(404, 'Dictionary not found');
    }
}
