<?php

namespace Tests\Feature;

use App\Models\QrUser;
use App\Models\User;
use App\Services\QrImageService;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QrAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        Storage::fake('public');

        $this->mock(QrImageService::class, function ($mock) {
            $mock->shouldReceive('png')->andReturn('fake-png-bytes');
        });
    }

    public function test_authenticated_user_can_generate_qr(): void
    {
        $user = User::query()->where('email', 'test@diplom.ru')->firstOrFail();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/qr/generate');

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('qr_users', 1);
    }

    public function test_owner_can_activate_qr_and_deactivate_it(): void
    {
        $user = User::query()->where('email', 'test@diplom.ru')->firstOrFail();
        Sanctum::actingAs($user);

        $qr = QrUser::create([
            'user_id' => $user->id,
            'unique_id' => (string) Str::uuid(),
            'path' => '/storage/img/qr-code/test.png',
            'active' => true,
        ]);

        $response = $this->postJson('/api/qr/check/'.$qr->unique_id);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertFalse($qr->fresh()->active);
    }

    public function test_foreign_qr_is_rejected(): void
    {
        $owner = User::query()->where('email', 'test@diplom.ru')->firstOrFail();
        $admin = User::query()->where('email', 'admin@diplom.ru')->firstOrFail();

        $qr = QrUser::create([
            'user_id' => $owner->id,
            'unique_id' => (string) Str::uuid(),
            'path' => '/storage/img/qr-code/test.png',
            'active' => true,
        ]);

        Sanctum::actingAs($admin);

        $this->postJson('/api/qr/check/'.$qr->unique_id)->assertForbidden();
        $this->assertTrue($qr->fresh()->active);
    }
}
