<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\HasContactRules;
use App\Models\Vacancy;
use Illuminate\Foundation\Http\FormRequest;

class VacancyRequest extends FormRequest
{
    use HasContactRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format' => 'required|in:'.implode(',', Vacancy::FORMATS),
            'employment_type' => 'required|in:'.implode(',', Vacancy::EMPLOYMENT_TYPES),
            'position_id' => 'required|numeric',
            'department_id' => 'nullable|numeric',
            'region_id' => 'required|exists:regions,id',
            'salary' => 'nullable|numeric',
            'experience' => 'nullable|numeric',
            'is_experience_required' => 'nullable|boolean',
            'company' => 'nullable|array',
            'description' => 'required|string',
            ...$this->contactRules()
        ];
    }
}
