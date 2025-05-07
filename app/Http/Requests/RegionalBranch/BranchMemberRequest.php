<?php

namespace App\Http\Requests\RegionalBranch;

use Illuminate\Foundation\Http\FormRequest;

class BranchMemberRequest extends FormRequest
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
            'members' => 'required|array',
            'members.*.id' => 'nullable|exists:branch_members,id',
            'members.*.name' => 'required|string',
            'members.*.description' => 'nullable|string',
            'members.*.image_file' => 'nullable|file|image|max:2048',
        ];
    }
}
