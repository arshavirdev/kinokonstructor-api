<?php

namespace App\Service\Shared;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ContactHandlerService
{
    public function handle(Model $model, array $contacts, User $user): void
    {
        $data = [
            'phone' => $contacts['phone'] ?? [],
            'email' => $contacts['email'] ?? [],
            'website' => $contacts['website'] ?? [],
            'socials' => $contacts['socials'] ?? [],
            'other' => $contacts['other'] ?? [],
        ];

        $contacts = $model->contacts;

        if (!$contacts->isEmpty()) {
            $model->contacts()->update($data);
            return;
        }

        $model->contacts()->create(array_merge(['user_id' => $user->id], $data));
    }
}
