<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_screen_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_public_registration_cannot_create_an_account(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_login_and_password_reset_remain_available(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_super_administrator_can_still_open_user_creation(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Administrator',
        ]);

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk();
    }
}
