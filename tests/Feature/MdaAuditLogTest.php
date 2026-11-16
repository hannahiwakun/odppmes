<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Mda;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MdaAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_mda_writes_an_audit_entry_without_contact_details(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('mdas.store'), [
                'name' => 'Ministry of Health',
                'code' => 'MOH',
                'email' => 'health@example.com',
                'phone' => '08031112222',
                'contact_person' => 'Records Officer',
                'address' => 'Akure',
                'focal_name' => 'Ada Okafor',
                'focal_department' => 'Planning',
                'focal_designation' => 'Director',
                'focal_supervisor' => 'Chief Bola',
                'focal_phone' => '08032223333',
                'focal_email' => 'ada@example.com',
                'status' => 1,
            ])
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('success');

        $mda = Mda::where('code', 'MOH')->firstOrFail();
        $log = $this->onlyLog();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('Created', $log->action);
        $this->assertSame('MDA', $log->module);
        $this->assertNotNull($log->created_at);
        $this->assertStringContainsString('MDA #'.$mda->id.' Ministry of Health (MOH)', $log->description);
        $this->assertStringContainsString('Actor user #'.$admin->id.', role Super Administrator', $log->description);
        $this->assertStringContainsString('Status: Active', $log->description);
        $this->assertStringNotContainsString('health@example.com', $log->description);
        $this->assertStringNotContainsString('08031112222', $log->description);
        $this->assertStringNotContainsString('Ada Okafor', $log->description);
        $this->assertStringNotContainsString('ada@example.com', $log->description);
    }

    public function test_updating_an_mda_records_safe_changes_and_hides_personal_values(): void
    {
        $admin = $this->admin();
        $mda = Mda::create([
            'name' => 'Ministry of Works',
            'code' => 'MOW',
            'email' => 'old@example.com',
            'phone' => '08030000000',
            'focal_department' => 'Roads',
            'focal_designation' => 'Officer',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('mdas.update', $mda), [
                'name' => 'Ministry of Infrastructure',
                'code' => 'MOI',
                'email' => 'new-secret@example.com',
                'phone' => '08039998888',
                'contact_person' => 'Records Officer',
                'address' => 'Akure',
                'focal_name' => 'Ada Okafor',
                'focal_department' => 'Planning',
                'focal_designation' => 'Director',
                'focal_supervisor' => 'Chief Bola',
                'focal_phone' => '08032223333',
                'focal_email' => 'ada@example.com',
                'alternate_email' => 'alternate-secret@example.com',
                'status' => 0,
            ])
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('success');

        $log = $this->onlyLog();

        $this->assertSame('Updated', $log->action);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertStringContainsString('Name: Ministry of Works -> Ministry of Infrastructure', $log->description);
        $this->assertStringContainsString('Code: MOW -> MOI', $log->description);
        $this->assertStringContainsString('Department: Roads -> Planning', $log->description);
        $this->assertStringContainsString('Focal designation: Officer -> Director', $log->description);
        $this->assertStringContainsString('Status: Active -> Inactive', $log->description);
        $this->assertStringContainsString('Email changed', $log->description);
        $this->assertStringContainsString('Phone changed', $log->description);
        $this->assertStringContainsString('Supervising director changed', $log->description);
        $this->assertStringContainsString('Alternate officer email changed', $log->description);
        $this->assertStringNotContainsString('old@example.com', $log->description);
        $this->assertStringNotContainsString('new-secret@example.com', $log->description);
        $this->assertStringNotContainsString('08030000000', $log->description);
        $this->assertStringNotContainsString('08039998888', $log->description);
        $this->assertStringNotContainsString('Chief Bola', $log->description);
        $this->assertStringNotContainsString('alternate-secret@example.com', $log->description);
        $this->assertFalse((bool) $mda->fresh()->status);
    }

    public function test_deactivating_an_mda_writes_an_audit_entry(): void
    {
        $admin = $this->admin();
        $mda = Mda::create([
            'name' => 'Ministry of Justice',
            'code' => 'MOJ',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('mdas.deactivate', $mda))
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('success');

        $log = $this->onlyLog();

        $this->assertSame('Deactivated', $log->action);
        $this->assertSame('MDA', $log->module);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertStringContainsString('MDA #'.$mda->id.' Ministry of Justice (MOJ)', $log->description);
        $this->assertStringContainsString('Status: Active -> Inactive', $log->description);
        $this->assertFalse((bool) $mda->fresh()->status);
    }

    public function test_deleting_an_empty_mda_keeps_the_audit_entry(): void
    {
        $admin = $this->admin();
        $mda = Mda::create([
            'name' => 'Empty Agency',
            'code' => 'EMP',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('mdas.destroy', $mda))
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('mdas', ['id' => $mda->id]);

        $log = $this->onlyLog();

        $this->assertSame('Deleted', $log->action);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertStringContainsString('MDA #'.$mda->id.' Empty Agency (EMP)', $log->description);
        $this->assertStringContainsString('The ministry record was removed', $log->description);
    }

    public function test_a_blocked_deletion_does_not_write_a_deletion_audit_entry(): void
    {
        $admin = $this->admin();
        $mda = Mda::create([
            'name' => 'Ministry of Works',
            'code' => 'MOW',
            'status' => true,
        ]);
        Project::create([
            'mda_id' => $mda->id,
            'project_title' => 'Road Project',
            'sector' => 'Infrastructure',
            'lga' => 'Ondo West',
        ]);

        $this->actingAs($admin)
            ->delete(route('mdas.destroy', $mda))
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('mdas', ['id' => $mda->id]);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_a_failed_audit_log_rolls_back_the_ministry_and_does_not_report_success(): void
    {
        $admin = $this->admin();

        AuditLog::creating(function () {
            throw new RuntimeException('Audit log failed.');
        });

        $response = $this->actingAs($admin)->post(route('mdas.store'), [
            'name' => 'Ministry of Health',
            'code' => 'MOH',
            'email' => 'health@example.com',
            'phone' => '08031112222',
            'contact_person' => 'Records Officer',
            'address' => 'Akure',
            'focal_name' => 'Ada Okafor',
            'focal_department' => 'Planning',
            'focal_designation' => 'Director',
            'focal_supervisor' => 'Chief Bola',
            'focal_phone' => '08032223333',
            'focal_email' => 'ada@example.com',
            'status' => 1,
        ]);

        $response->assertStatus(500);
        $response->assertSessionMissing('success');
        $this->assertDatabaseMissing('mdas', ['code' => 'MOH']);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_unauthorized_users_cannot_change_an_mda_or_its_audit_log(): void
    {
        $officer = User::factory()->create(['role' => 'M&E Officer']);
        $mda = Mda::create([
            'name' => 'Ministry of Health',
            'code' => 'MOH',
            'status' => true,
        ]);

        $this->actingAs($officer)
            ->post(route('mdas.store'), [
                'name' => 'Should Not Exist',
                'code' => 'NOPE',
                'status' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($officer)
            ->put(route('mdas.update', $mda), [
                'name' => 'Changed Name',
                'code' => 'MOH',
                'status' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($officer)
            ->patch(route('mdas.deactivate', $mda))
            ->assertForbidden();

        $this->actingAs($officer)
            ->delete(route('mdas.destroy', $mda))
            ->assertForbidden();

        $this->actingAs($officer)
            ->get(route('audit.logs'))
            ->assertForbidden();

        $this->assertSame('Ministry of Health', $mda->fresh()->name);
        $this->assertTrue((bool) $mda->fresh()->status);
        $this->assertDatabaseMissing('mdas', ['code' => 'NOPE']);
        $this->assertDatabaseCount('audit_logs', 0);

        $admin = $this->admin();
        $this->actingAs($admin)
            ->post(route('mdas.store'), [
                'name' => 'Ministry of Agriculture',
                'code' => 'MOA',
                'email' => 'agri@example.com',
                'phone' => '08031112222',
                'contact_person' => 'Records Officer',
                'address' => 'Akure',
                'focal_name' => 'Ada Okafor',
                'focal_department' => 'Planning',
                'focal_designation' => 'Director',
                'focal_supervisor' => 'Chief Bola',
                'focal_phone' => '08032223333',
                'focal_email' => 'ada@example.com',
                'status' => 1,
            ])
            ->assertRedirect(route('mdas.index'));

        $log = $this->onlyLog();

        $this->actingAs($admin)
            ->post('/audit-logs', [
                'action' => 'Tampered',
                'module' => 'MDA',
                'description' => 'Forged entry',
            ])
            ->assertStatus(405);

        $this->actingAs($admin)
            ->put('/audit-logs/'.$log->id, [
                'action' => 'Tampered',
                'description' => 'Forged change',
            ])
            ->assertNotFound();

        $this->actingAs($officer)
            ->put('/audit-logs/'.$log->id, [
                'action' => 'Tampered',
            ])
            ->assertNotFound();

        $this->assertSame('Created', $log->fresh()->action);
        $this->assertSame('MDA', $log->fresh()->module);
        $this->assertStringNotContainsString('Forged', (string) $log->fresh()->description);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'Super Administrator']);
    }

    private function onlyLog(): AuditLog
    {
        $this->assertDatabaseCount('audit_logs', 1);

        return AuditLog::firstOrFail();
    }
}
