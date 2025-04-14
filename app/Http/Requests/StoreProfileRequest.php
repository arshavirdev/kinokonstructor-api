<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
//        return Auth::user()->profile === null;
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
            'middlename' => 'string|nullable',
            'city' => 'string',
            'birthday' => 'date',
            'occupation_id' => 'array|min:1',
            'occupation_ids.*' => "exists:occupations,id",
            'phone' => [
                Rule::unique('profiles', 'phone')
            ],
            "additional_information" => 'string|nullable',

            'org' => 'nullable',
            'org.reg_id' => 'numeric|required_unless:org,null',
            'org.name' => 'string|required_unless:org,null',
            'org.position' => 'string|required_unless:org,null',

            'entrepreneur' => 'nullable',
            'entrepreneur.reg_id' => 'numeric|required_unless:entrepreneur,null',

            'regions.*' => 'integer',

            'portfolio' => 'string|nullable',
            'mass_media_mentions' => 'string|nullable',

            'socials_vk' => 'string|nullable',
            'socials_tg' => 'string|nullable',
            'socials_ok' => 'string|nullable',

            'privacy_hide' => 'array',
            'privacy_hide.*' => 'string|in:phone,email,website,socials',

            'avatar' => 'file|nullable',
            'attachments.*' => 'nullable',

            'contacts' => 'array',
            'contacts.phone' => 'array',
            'contacts.email' => 'array',
            'contacts.website' => 'array',
            'contacts.socials' => 'array',
            'contacts.other' => 'array',

            'education.*.id' => 'integer|nullable',
            'education.*.institution' => 'string',
            'education.*.speciality' => 'string',
            'education.*.start' => 'integer|between:1900,2100',
            'education.*.end' => 'integer|between:1900,2100',

            'experience.*.id' => 'integer|nullable',
            'experience.*.company' => 'string',
            'experience.*.position' => 'string',
            'experience.*.start' => 'integer|between:1900,2100',
            'experience.*.end' => 'integer|between:1900,2100',

            'projects.*.id' => 'integer|nullable',
            'projects.*.name' => 'string',
            'projects.*.position' => 'string',
            'projects.*.start' => 'integer|between:1900,2100',
            'projects.*.end' => 'integer|between:1900,2100',
            'projects.*.description' => 'string',
        ];
    }
}
