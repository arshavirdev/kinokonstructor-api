<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'category' => 'sometimes|nullable|array',
            'category.*' => 'string',
            'region_id' => 'required|exists:regions,id',
            'parameters' => 'sometimes|nullable|array',
            'company' => 'sometimes|nullable|array',
            'images_files' => 'sometimes|nullable|array',
            'files_section' => 'sometimes|nullable|array',
            // Contacts
            'contacts' => 'nullable|array',
            'contacts.phone' => 'nullable|array',
            'contacts.email' => 'nullable|array',
            'contacts.website' => 'nullable|array',
            'contacts.socials' => 'nullable|array',
            'contacts.other' => 'nullable|array',
            'contacts.telVisible' => 'nullable|boolean',
            'contacts.emailVisible' => 'nullable|boolean',
        ];
    }
}
