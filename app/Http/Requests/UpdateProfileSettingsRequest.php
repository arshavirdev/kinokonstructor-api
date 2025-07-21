<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileSettingsRequest extends FormRequest
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
            'email' => [
                'email',
                Rule::unique('users', 'email')->ignore(auth()->id())
            ],
            'regions.*' => 'integer',
            'city' => 'string',

            'preferences' => 'sometimes|nullable|array',
            'preferences.notify_chat_messages' => 'boolean',
            'preferences.notify_industry_news' => 'boolean',
            'preferences.notify_project_responses' => 'boolean'
        ];
    }
}
