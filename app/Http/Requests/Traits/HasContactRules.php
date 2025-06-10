<?php

namespace App\Http\Requests\Traits;

trait HasContactRules
{
    protected function contactRules(): array
    {
        return [
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
