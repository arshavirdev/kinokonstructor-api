<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_register()
    {
        Notification::fake();
        Mail::fake();

        $user = $this->registerUser();

        $this->assertFalse($user->hasVerifiedEmail());

        $getUserResponse = $this->getJson('/api/dashboard');
        $getUserResponse->assertForbidden()->assertJsonPath('message', 'Your email address is not verified.');

        Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);
            parse_str(parse_url($mail->actionUrl, PHP_URL_QUERY), $params);

            $this->actingAs($user)->postJson(
                "/api/auth/email/verify/{$params['id']}/{$params['hash']}?" . http_build_query($params)
            );
            // User should have verified their email
            $this->assertTrue(User::find($user->id)->hasVerifiedEmail());
            return true;
        });
    }

    private function registerUser()
    {
        $dto = [
            'username' => 'test_buddy',
            'email' => 'test@buddy.com',
            'password' => 'test_password',
            'password_confirmation' => 'test_password',
            'allow_newsletter' => false
        ];
        User::where('email', $dto['email'])->delete();
        $response = $this->postJson('/api/auth/register', $dto);
        $response->assertCreated();
        return User::whereEmail($dto['email'])->first();
    }
}
