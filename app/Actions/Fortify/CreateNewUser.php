<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'email' => ['required', 'string', 'email', 'max:120', Rule::unique(User::class),],
            'username' => ['required_without:member_id', 'string', 'max:64', Rule::unique(User::class)],
            'allow_newsletter' => ['boolean'],
            'password' => $this->passwordRules(),
        ])->validate();


        $id = $input['member_id'] ?? null;
        $data = [
            'username' => $input['username'] ?? $id,
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'allow_newsletter' => (bool)$input['allow_newsletter'],
        ];

        if ($id) {
            return $this->createByMember($id, $data);
        }

        return $this->createBasic($data);
    }

    private function createByMember($id, $data)
    {
        $profile = Profile::where('member_id', $id)->whereNull('user_id')->first();
        abort_unless($profile, 404, 'Member not found');

        $user = new User($data);
        $user->role = 'specialist';
        $user->markEmailAsVerified();
        $user->save();

        $user->profile()->save($profile);

        return $user;
    }

    private function createBasic($data)
    {
        Validator::validate($data, ['username' => [function (string $attribute, mixed $value, \Closure $fail) {
            if (is_numeric($value)) {
                $fail("Поле не может содержать только цифры");
            }
        }]]);
        return User::create($data);
    }
}
