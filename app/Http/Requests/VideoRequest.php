<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|array',
//            'details' => 'nullable|string',
            // 'video_file' => 'sometimes|file|mimetypes:video/mp4,video/avi,video/mpeg,',
            // 'image_file' => 'sometimes|file|mimetypes:image/jpg,image/jpeg,image/png',
        ];
    }
}
