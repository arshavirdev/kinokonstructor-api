<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        return true;
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
            'firstname' => 'alpha',
            'lastname' => 'alpha',
            'middlename' => 'alpha|nullable',
            'city' => 'string',
            'birthday' => 'date',
            'occupation_id' => "exists:occupations,id",
            'phone' => [
                Rule::unique('profiles', 'phone')->ignore(Auth::user()->profile->id)
            ],

            'is_org' => 'boolean',
            'org_reg_id' => 'numeric|exclude_if:is_org,false',
            'org_name' => 'string|exclude_if:is_org,false',
            'org_position' => 'string|exclude_if:is_org,false',

            'is_entrepreneur' => 'boolean',
            'entrepreneur_reg_id' => 'numeric|exclude_if:is_entrepreneur,false',

            'regions.*' => 'integer',

            'portfolio' => 'string|nullable',
            'mass_media_mentions' => 'string|nullable',

            'socials_vk' => 'string|nullable',
            'socials_tg' => 'string|nullable',
            'socials_ok' => 'string|nullable',

            'avatar' => 'file|nullable',
            'attachments.*' => 'file|nullable',

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
