<?php

namespace App\Http\Controllers;

use App\Models\Occupation;

class DictionaryController extends Controller
{
    public function show($dictionary)
    {
        if ($dictionary === 'occupations') return Occupation::all();
        abort(404, 'Dictionary not found');
    }
}
