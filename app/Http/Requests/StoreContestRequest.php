<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContestRequest extends FormRequest
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
            'title' => 'required|string|min:2|max:255',
            'type' => 'nullable|string|max:255',
            'years_held' => 'nullable|numeric',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            
            // Gallery (Multiple Images)
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

            'conditions' => 'nullable|string',

            'deadlines' => 'nullable',
            'deadlines.*.title' => 'nullable|string|max:255',
            'deadlines.*.date' => 'nullable|date',

            'prizes' => 'nullable|string',
            'adjudicator' => 'nullable|string',
            'organizers' => 'nullable|string',
            
            // Documents (Multiple Files)
            'documents' => 'nullable|array',
            'documents.*' => 'nullable',

            'online_application' => 'nullable|string|max:255',
            // Logo (Single File)
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video' => 'nullable|string',

            // Photo Gallery (Multiple Images)
            'photo_gallery' => 'nullable|array',
            'photo_gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

            // Partners (Multiple Images)
            'partners' => 'nullable|array',
            'partners.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

            'contacts.website' => 'nullable|string|max:255',
            'contacts.social_media' => 'nullable|string|max:255',
            'contacts.email' => 'nullable|string|max:255',
            'contacts.phone' => 'nullable|string|max:20',
            'contacts.postal_address' => 'nullable|string|max:255',
            'contacts.button_name' => 'nullable|string|max:255',
        ];
    }
}
