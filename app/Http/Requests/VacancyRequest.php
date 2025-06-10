<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\HasContactRules;
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
            'format' => 'required|in:remote,office,hybrid',
            'position_id' => 'required|numeric',
            'description' => 'required|string',
            ...$this->contactRules()
        ];
    }
}
