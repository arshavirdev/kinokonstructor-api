<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\HasContactRules;
use App\Models\Resume;
use Illuminate\Foundation\Http\FormRequest;

class StoreResumeRequest extends FormRequest
{
    use HasContactRules;

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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'position_id' => 'required|exists:occupations,id',
            'work_format' => 'required|in:' . implode(',', Resume::FORMATS),
            'salary_expectation' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'experience_years' => 'nullable|numeric|min:0',
            'experience_description' => 'nullable|string',
            'bio' => 'nullable|string',
            ...$this->contactRules()
        ];
    }
}
