<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_login_with_email()
    {
        $dto = [
            'login' => 'admin@admin.com',
            'password' => 'password'
        ];
        $response = $this->postJson('/api/auth/login', $dto);
        $response->assertStatus(200)->assertCookie('kinokonstruktor_session');

        $user = $this->getJson('/api/user');
        $this->assertTrue($user['email'] === $dto['login']);
    }

    public function test_login_with_username()
    {
        $dto = [
            'login' => 'admin',
            'password' => 'password'
        ];
        $response = $this->postJson('/api/auth/login', $dto);
        $response->assertStatus(200)->assertCookie('kinokonstruktor_session');

        $user = $this->getJson('/api/user');
        $this->assertTrue($user['username'] === $dto['login']);
    }

    public function test_unsuccessfull_login()
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'admin',
            'password' => 'wrong'
        ]);
        $response->assertStatus(422);

        $user = $this->getJson('/api/user');
        $user->assertStatus(401);
    }
}
