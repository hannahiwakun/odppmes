<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_last_super_administrator_cannot_change_their_role(): void
    {
        $admin = $this->administrator('Only Admin');

        $this->actingAs($admin)
            ->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'name' => 'Only Admin',
                'email' => $admin->email,
                'role' => 'Viewer',
            ])
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHasErrors([
                'role' => 'This role was not changed because it would leave the portal without a Super Administrator.',
            ]);

        $this->assertSame('Super Administrator', $admin->fresh()->role);
        $this->assertSame('Only Admin', $admin->fresh()->name);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_the_last_super_administrator_can_still_update_their_name(): void
    {
        $admin = $this->administrator('Only Admin');

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'name' => 'Kept Admin',
                'email' => $admin->email,
                'role' => 'Super Administrator',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $admin->refresh();
        $this->assertSame('Kept Admin', $admin->name);
        $this->assertSame('Super Administrator', $admin->role);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'Updated',
            'module' => 'User',
        ]);
    }

    public function test_another_super_administrator_can_change_a_colleague_role(): void
    {
        $admin = $this->administrator('First Admin');
        $second = $this->administrator('Second Admin');

        $this->actingAs($second)
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'M&E Officer',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSame('M&E Officer', $admin->fresh()->role);
        $this->assertSame('Super Administrator', $second->fresh()->role);
    }

    public function test_the_last_super_administrator_cannot_delete_their_profile(): void
    {
        $admin = $this->administrator('Only Admin');

        $this->actingAs($admin)
            ->from('/profile')
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')
            ->assertSessionHasErrorsIn('userDeletion', [
                'password' => 'This account was not deleted because it is the last Super Administrator.',
            ]);

        $this->assertAuthenticated();
        $this->assertSame('Super Administrator', $admin->fresh()->role);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_a_permitted_profile_deletion_is_audited_and_ends_the_session(): void
    {
        $viewer = User::factory()->create([
            'role' => 'Viewer',
            'name' => 'Ada Viewer',
        ]);

        $this->actingAs($viewer)
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($viewer->fresh());

        $log = AuditLog::query()->where('module', 'User')->where('action', 'Deleted')->first();
        $this->assertNotNull($log);
        $this->assertSame('Deleted user: Ada Viewer', $log->description);
        $this->assertNull($log->user_id);
    }

    public function test_a_second_super_administrator_can_delete_their_own_profile(): void
    {
        $admin = $this->administrator('First Admin');
        $second = $this->administrator('Second Admin');

        $this->actingAs($second)
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($second->fresh());
        $this->assertSame('Super Administrator', $admin->fresh()->role);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Deleted',
            'module' => 'User',
            'description' => 'Deleted user: Second Admin',
        ]);
    }

    public function test_deleting_your_own_account_from_user_management_is_an_error(): void
    {
        $admin = $this->administrator('Only Admin');

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'You cannot delete your own account.')
            ->assertSessionMissing('success');

        $this->assertSame('Super Administrator', $admin->fresh()->role);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_another_super_administrator_can_delete_a_colleague_without_blockers(): void
    {
        $admin = $this->administrator('First Admin');
        $second = $this->administrator('Second Admin');

        $this->actingAs($second)
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
        $this->assertSame('Super Administrator', $second->fresh()->role);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $second->id,
            'action' => 'Deleted',
            'module' => 'User',
            'description' => 'Deleted user: First Admin',
        ]);
    }

    public function test_a_failed_audit_rolls_back_user_creation(): void
    {
        $admin = $this->administrator('Only Admin');
        $this->failNextAudit();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Person',
            'email' => 'new-person@example.com',
            'role' => 'Viewer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(500);
        $response->assertSessionMissing('success');
        $this->assertDatabaseMissing('users', ['email' => 'new-person@example.com']);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_a_failed_audit_rolls_back_a_user_update(): void
    {
        $admin = $this->administrator('Only Admin');
        $officer = User::factory()->create([
            'role' => 'M&E Officer',
            'name' => 'Field Officer',
        ]);
        $this->failNextAudit();

        $response = $this->actingAs($admin)->put(route('users.update', $officer), [
            'name' => 'Changed Name',
            'email' => $officer->email,
            'role' => 'Viewer',
        ]);

        $response->assertStatus(500);
        $response->assertSessionMissing('success');
        $officer->refresh();
        $this->assertSame('Field Officer', $officer->name);
        $this->assertSame('M&E Officer', $officer->role);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_a_failed_audit_rolls_back_an_administrative_deletion(): void
    {
        $admin = $this->administrator('Only Admin');
        $viewer = User::factory()->create(['role' => 'Viewer']);
        $this->failNextAudit();

        $response = $this->actingAs($admin)->delete(route('users.destroy', $viewer));

        $response->assertStatus(500);
        $response->assertSessionMissing('success');
        $this->assertNotNull($viewer->fresh());
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_a_failed_profile_audit_keeps_the_account_and_the_session(): void
    {
        $viewer = User::factory()->create(['role' => 'Viewer']);
        $this->failNextAudit();

        $response = $this->actingAs($viewer)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertStatus(500);
        $this->assertAuthenticated();
        $this->assertNotNull($viewer->fresh());
        $this->assertDatabaseCount('audit_logs', 0);
    }

    private function administrator(string $name): User
    {
        return User::factory()->create([
            'role' => 'Super Administrator',
            'name' => $name,
        ]);
    }

    private function failNextAudit(): void
    {
        AuditLog::creating(function () {
            throw new RuntimeException('Audit log failed.');
        });
    }
}
