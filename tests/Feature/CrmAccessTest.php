<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }

    public function test_admin_can_open_dashboard(): void
    {
        $admin = User::query()->where('email', 'admin@diplom.ru')->firstOrFail();

        $this->actingAs($admin)
            ->get('/')
            ->assertOk();
    }

    public function test_guest_cannot_open_users_page(): void
    {
        $this->get('/users')->assertRedirect(route('login'));
    }

    public function test_admin_can_ban_and_unban_user(): void
    {
        $admin = User::query()->where('email', 'admin@diplom.ru')->firstOrFail();
        $user = User::query()->where('email', 'test@diplom.ru')->firstOrFail();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->actingAs($admin)
            ->post(route('users.ban', ['id' => $user->id]))
            ->assertRedirect();

        $this->assertFalse($user->fresh()->active);

        $this->actingAs($admin)
            ->post(route('users.unban', ['id' => $user->id]))
            ->assertRedirect();

        $this->assertTrue($user->fresh()->active);
    }
}
