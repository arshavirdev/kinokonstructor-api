<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\HasContactRules;
use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    use HasContactRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'category' => 'sometimes|nullable|array',
            'category.*' => 'string',
            'region_id' => 'required|exists:regions,id',
            'parameters' => 'sometimes|nullable|array',
            'company' => 'sometimes|nullable|array',
            'images_files' => 'sometimes|nullable|array',
            'files_section' => 'sometimes|nullable|array',
            ...$this->contactRules(),
        ];
    }
}
