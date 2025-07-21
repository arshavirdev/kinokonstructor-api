<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'gender' => 'in:m,f',
            'firstname' => 'string',
            'lastname' => 'string',
            'username' => [
                Rule::unique('users', 'username')->ignore(auth()->id())
            ],
            'city' => 'nullable|string',
            'birthday' => 'nullable|date',
            'occupation_id' => 'array|min:1',
            'occupation_ids.*' => "exists:occupations,id",
            "additional_information" => 'string|nullable',
         
            'org' => 'nullable',
            'org.reg_id' => 'nullable|numeric',
            'org.name' => 'nullable|string',
            'org.position' => 'nullable|string',

            'entrepreneur' => 'nullable',
            'entrepreneur.reg_id' => 'nullable|numeric',

            'privacy_hide' => 'array',
            'privacy_hide.*' => 'string|in:phone,email,website,socials',

            'avatar' => 'file|nullable',
            'attachments' => 'array|nullable',
            'attachments.*' => 'file|nullable',

            'contacts' => 'array',
            'contacts.phone' => 'array',
            'contacts.email' => 'array',
            'contacts.website' => 'array',
            'contacts.socials' => 'array',
            'contacts.other' => 'array'
        ];
    }
}
