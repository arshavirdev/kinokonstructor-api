<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        function array_map_values(callable $f, array $a): array
        {
            return array_column(array_map($f, array_keys($a), $a), 1, 0);
        }

        $addOptional = fn($key, $value) => [$key, 'nullable|' . $value];
        $makeOptional = fn(array $array) => array_map_values($addOptional, $array);

        return array_merge(Project::$validation['basic'], $makeOptional(Project::$validation['additional']));
    }
}
