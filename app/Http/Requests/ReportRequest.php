<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'entity_type' => 'required|string|in:Project',
            'entity_id' => 'required|integer',
            'name' => 'required|string',
            'email' => 'required|email',
            'text' => 'required|string',
        ];
    }
}
