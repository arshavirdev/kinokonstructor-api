<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequestNEW extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Project base fields
            'title' => 'required|string|max:255',
            'logline' => 'required|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|string|in:movie,series',
            'genre_type' => 'required|string|in:documentary,fictional',
            'chronography' => 'required|integer|min:1',
            'genres' => 'required|array',
            'genres.*' => 'required|integer',
            'years_rating' => 'required|string|max:10',
            'region_id' => 'required|integer|exists:regions,id',
            'city' => 'sometimes|string|max:255',
            'series_count' => 'nullable|integer|min:1',

            // Custom members (team)
            'custom_members' => 'sometimes|array',
            'custom_members.*.full_name' => 'required|string|max:255',
            'custom_members.*.role' => 'required|array',
            'custom_members.*.additional_information' => 'sometimes|string|max:1000',

            // Requests section
            'requests' => 'nullable|array',
            'requests.*' => 'array',
            'requests.*.*.name' => 'nullable|string|max:255',
            'requests.*.*.location' => 'nullable|string|max:255',
            'requests.*.*.season' => 'sometimes|array',
            'requests.*.*.category' => 'sometimes|array',
            'requests.*.*.info' => 'nullable|string',
            'requests.*.*.docs_files' => 'sometimes|array',
            'requests.*.*.images_files' => 'sometimes|array',
//            'requests.*.*.docs_files.*' => 'sometimes|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
//            'requests.*.*.images_files.*' => 'sometimes|file|mimes:jpg,jpeg,png|max:5120',

            // Project contacts
            'project_contacts' => 'sometimes|array',
            'project_contacts.phone' => 'nullable|array',
            'project_contacts.email' => 'nullable|array',
            'project_contacts.website' => 'nullable|array',
            'project_contacts.socials' => 'nullable|array',
            'project_contacts.other' => 'nullable|array',
            'project_contacts.telVisible' => 'sometimes|boolean',
            'project_contacts.emailVisible' => 'sometimes|boolean',

            // Applicant info
            'applicant_full_name' => 'required|string|max:255',
            'applicant_occupation_ids' => 'required|array',
            'applicant_occupation_ids.*' => 'integer|exists:occupations,id',
            'applicant_contacts' => 'sometimes|array',
            'applicant_contacts.phone' => 'nullable|array',
            'applicant_contacts.email' => 'nullable|array',
            'applicant_contacts.website' => 'nullable|array',
            'applicant_contacts.socials' => 'nullable|array',
            'applicant_contacts.other' => 'nullable|array',
            'applicant_contacts.telVisible' => 'sometimes|boolean',
            'applicant_contacts.emailVisible' => 'sometimes|boolean',

            // Files uploads
            'files_section' => 'nullable|array',
            'files_section.*' => 'array',
            'files_section.*.*.files' => 'nullable|array',
//            'files_section.*.*.files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',

            // Project images
            'project_images' => 'nullable|array',
//            'project_images.*' => 'file|mimes:jpg,jpeg,png|max:5120',
        ];
    }
}
