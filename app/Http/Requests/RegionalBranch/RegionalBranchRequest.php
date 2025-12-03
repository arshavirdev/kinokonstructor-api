<?php

namespace App\Http\Requests\RegionalBranch;

use App\Http\Requests\Traits\HasContactRules;
use Illuminate\Foundation\Http\FormRequest;

class RegionalBranchRequest extends FormRequest
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
            'title' => 'required|string',
            'description' => 'required|string',
            'year' => 'sometimes|nullable|numeric',
            'region_ids' => 'required|array',
            'region_ids.*' => 'required|exists:regions,id',
            'city' => 'sometimes|nullable|string',
            'manager' => 'sometimes|nullable|array',
            'address' => 'sometimes|nullable|string',
            ...$this->contactRules()
        ];
    }
}
