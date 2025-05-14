<?php

namespace App\Http\Requests\RegionalBranch;

use Illuminate\Foundation\Http\FormRequest;

class RegionalBranchRequest extends FormRequest
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
            'title' => 'required|string',
            'description' => 'required|string',
            'year' => 'sometimes|nullable|numeric',
            'region_id' => 'required|exists:regions,id',
            'city' => 'sometimes|nullable|string',
            'manager' => 'sometimes|nullable|array',
            'address' => 'sometimes|nullable|string',
            'contacts' => 'sometimes|nullable|array',
            'contacts.phone' => 'nullable|array',
            'contacts.email' => 'nullable|array',
            'contacts.website' => 'nullable|array',
            'contacts.socials' => 'nullable|array',
            'contacts.other' => 'nullable|array',
        ];
    }
}
