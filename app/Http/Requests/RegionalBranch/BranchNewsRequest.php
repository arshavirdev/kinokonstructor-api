<?php

namespace App\Http\Requests\RegionalBranch;

use Illuminate\Foundation\Http\FormRequest;

class BranchNewsRequest extends FormRequest
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
            'news' => 'required|array',
            'news.*.id' => 'nullable|exists:branch_news,id',
            'news.*.title' => 'required|string',
            'news.*.category' => 'sometimes|array',
            'news.*.short_description' => 'required|string',
            'news.*.description' => 'required|string',
            'news.*.image_file' => 'sometimes|file|image|max:2048',
        ];
    }
}
