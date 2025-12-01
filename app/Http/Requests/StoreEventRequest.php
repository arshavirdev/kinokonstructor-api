<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\HasContactRules;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'location' => 'nullable|string',
            'external_link' => 'nullable|string',
            'date' => 'nullable|date',
            'category' => 'required|array',
            'format' => 'required|string',
            'parameters' => 'nullable|array',
            'company' => 'nullable|array',
            'is_recorded' => 'nullable|boolean',
            'region_ids' => 'nullable|array',
            Event::IMAGES_FILES => 'nullable|array',
            Event::FILES => 'nullable|array',
            ...$this->contactRules()
        ];
    }
}
