<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }

    public function test_user_can_login_via_api(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@diplom.ru',
            'password' => 'pa$$w0rd123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'message', 'success']);
    }

    public function test_banned_user_cannot_login(): void
    {
        User::query()->where('email', 'test@diplom.ru')->update(['active' => false]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@diplom.ru',
            'password' => 'pa$$w0rd123',
        ]);

        $response->assertForbidden();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@diplom.ru',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
    }
}
