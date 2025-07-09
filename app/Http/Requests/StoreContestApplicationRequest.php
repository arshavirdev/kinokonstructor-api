<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContestApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "project_id"=> "required|integer|exists:projects,id",
            "description"=> "nullable|string",

            // Files uploads
            'files_section' => 'nullable|array',
            'files_section.*' => 'array',
            'files_section.*.*.files' => 'sometimes|array',

            // Contest images
            'images_files' => 'sometimes|array',
        ];
    }
}
