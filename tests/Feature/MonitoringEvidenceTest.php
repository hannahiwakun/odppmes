<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\MonitoringEvidence;
use App\Models\Project;
use App\Models\User;
use App\Support\OperationalStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MonitoringEvidenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorised_officers_can_upload_multiple_photographs_with_captions(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::fake('public');

        $officer = $this->officer();
        $visit = $this->visit($officer);

        $this->actingAs($officer)
            ->from(route('monitorings.show', $visit))
            ->post(route('monitorings.evidence.store', $visit), [
                'files' => [
                    UploadedFile::fake()->image('front.jpg'),
                    UploadedFile::fake()->image('side.png'),
                ],
                'captions' => [
                    'Clinic front',
                    'Side elevation',
                ],
            ])
            ->assertRedirect(route('monitorings.show', $visit))
            ->assertSessionHas('success');

        $this->assertSame(2, $visit->evidence()->count());
        $visit->evidence->each(function (MonitoringEvidence $item) {
            $this->assertSame(MonitoringEvidence::TYPE_PHOTO, $item->file_type);
            $this->assertTrue(MonitoringEvidence::isSafePath($item->file_path));
            Storage::disk(OperationalStorage::DISK)->assertExists($item->file_path);
            Storage::disk('public')->assertMissing($item->file_path);
            $this->assertStringStartsWith('monitorings/evidence/', $item->file_path);
            $this->assertFalse(str_starts_with($item->file_path, 'cms/media/'));
        });

        $this->assertDatabaseHas('monitoring_evidence', ['caption' => 'Clinic front']);
        $this->assertDatabaseHas('monitoring_evidence', ['caption' => 'Side elevation']);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Created',
            'module' => 'Monitoring Evidence',
        ]);
        $this->assertSame(2, AuditLog::query()->where('module', 'Monitoring Evidence')->count());
    }

    public function test_an_mp4_video_is_stored_privately_and_played_for_authorised_users_only(): void
    {
        Storage::fake(OperationalStorage::DISK);
        $officer = $this->officer();
        $visit = $this->visit($officer);
        $video = $this->mp4Upload('visit.mp4');

        $this->actingAs($officer)
            ->post(route('monitorings.evidence.store', $visit), [
                'files' => [$video],
                'captions' => ['Site walk-through'],
            ])
            ->assertRedirect(route('monitorings.show', $visit));

        $item = $visit->evidence()->firstOrFail();
        $this->assertSame(MonitoringEvidence::TYPE_VIDEO, $item->file_type);
        $this->assertSame('video/mp4', $item->mime_type);
        Storage::disk(OperationalStorage::DISK)->assertExists($item->file_path);

        $this->actingAs($officer)
            ->get(route('monitorings.evidence.file', [$visit, $item]))
            ->assertOk()
            ->assertHeader('content-type', 'video/mp4');

        $this->actingAs($officer)
            ->get(route('monitorings.evidence.download', [$visit, $item]))
            ->assertOk();

        $this->actingAs($officer)
            ->get(route('monitorings.show', $visit))
            ->assertOk()
            ->assertSee('Site walk-through', false)
            ->assertSee(route('monitorings.evidence.file', [$visit, $item]), false)
            ->assertDontSee('/storage/monitorings', false);
    }

    public function test_invalid_and_oversized_files_are_rejected(): void
    {
        Storage::fake(OperationalStorage::DISK);
        $officer = $this->officer();
        $visit = $this->visit($officer);

        $this->actingAs($officer)
            ->from(route('monitorings.show', $visit))
            ->post(route('monitorings.evidence.store', $visit), [
                'files' => [UploadedFile::fake()->create('payload.exe', 20, 'application/octet-stream')],
            ])
            ->assertRedirect(route('monitorings.show', $visit))
            ->assertSessionHasErrors('files');

        $this->actingAs($officer)
            ->from(route('monitorings.show', $visit))
            ->post(route('monitorings.evidence.store', $visit), [
                'files' => [UploadedFile::fake()->create('clip.svg', 20, 'image/svg+xml')],
            ])
            ->assertSessionHasErrors('files');

        $this->actingAs($officer)
            ->from(route('monitorings.show', $visit))
            ->post(route('monitorings.evidence.store', $visit), [
                'files' => [UploadedFile::fake()->image('huge.jpg')->size(5121)],
            ])
            ->assertSessionHasErrors('files');

        $this->assertSame(0, $visit->evidence()->count());
        $this->assertSame(102400, MonitoringEvidence::VIDEO_MAX_KILOBYTES);
        $this->assertSame(5120, MonitoringEvidence::PHOTO_MAX_KILOBYTES);
    }

    public function test_guests_viewers_and_development_partners_cannot_access_evidence(): void
    {
        Storage::fake(OperationalStorage::DISK);
        $officer = $this->officer();
        $visit = $this->visit($officer);
        $item = $this->storedPhoto($visit, $officer);

        $this->get(route('monitorings.evidence.file', [$visit, $item]))
            ->assertRedirect(route('login'));
        $this->get(route('monitorings.evidence.download', [$visit, $item]))
            ->assertRedirect(route('login'));
        $this->post(route('monitorings.evidence.store', $visit), [
            'files' => [UploadedFile::fake()->image('x.jpg')],
        ])->assertRedirect(route('login'));

        $this->actingAs($this->user('Viewer'))
            ->get(route('monitorings.evidence.file', [$visit, $item]))
            ->assertForbidden();

        $partnerOrg = DevelopmentPartner::create([
            'partner_name' => 'Alpha Fund',
            'partner_type' => 'Multilateral',
            'status' => true,
        ]);
        $partner = User::factory()->create([
            'role' => 'Development Partner',
            'development_partner_id' => $partnerOrg->id,
        ]);

        $this->actingAs($partner)
            ->get(route('monitorings.evidence.file', [$visit, $item]))
            ->assertForbidden();
        $this->actingAs($partner)
            ->get(route('monitorings.evidence.download', [$visit, $item]))
            ->assertForbidden();
        $this->actingAs($partner)
            ->post(route('monitorings.evidence.store', $visit), [
                'files' => [UploadedFile::fake()->image('x.jpg')],
            ])
            ->assertForbidden();
    }

    public function test_an_mda_officer_cannot_open_another_ministrys_evidence(): void
    {
        Storage::fake(OperationalStorage::DISK);
        $healthOfficer = $this->officer();
        $works = $this->ministry('Ministry of Works', 'MOW');
        $worksOfficer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $works->id,
        ]);
        $visit = $this->visit($healthOfficer);
        $item = $this->storedPhoto($visit, $healthOfficer);

        $this->actingAs($worksOfficer)
            ->get(route('monitorings.show', $visit))
            ->assertNotFound();
        $this->actingAs($worksOfficer)
            ->get(route('monitorings.evidence.file', [$visit, $item]))
            ->assertNotFound();
        $this->actingAs($worksOfficer)
            ->post(route('monitorings.evidence.store', $visit), [
                'files' => [UploadedFile::fake()->image('x.jpg')],
            ])
            ->assertNotFound();
    }

    public function test_only_super_administrators_and_me_officers_can_delete_evidence(): void
    {
        Storage::fake(OperationalStorage::DISK);
        $officer = $this->officer();
        $visit = $this->visit($officer);
        $item = $this->storedPhoto($visit, $officer, 'clinic.jpg');

        $this->actingAs($officer)
            ->delete(route('monitorings.evidence.destroy', [$visit, $item]))
            ->assertForbidden();

        $this->assertDatabaseHas('monitoring_evidence', ['id' => $item->id]);
        Storage::disk(OperationalStorage::DISK)->assertExists($item->file_path);

        $admin = $this->user('Super Administrator');
        $this->actingAs($admin)
            ->delete(route('monitorings.evidence.destroy', [$visit, $item]))
            ->assertRedirect(route('monitorings.show', $visit));

        $this->assertDatabaseMissing('monitoring_evidence', ['id' => $item->id]);
        Storage::disk(OperationalStorage::DISK)->assertMissing($item->file_path);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Deleted',
            'module' => 'Monitoring Evidence',
            'user_id' => $admin->id,
        ]);

        $second = $this->storedPhoto($visit, $officer, 'later.jpg');
        $meo = $this->user('M&E Officer');
        $this->actingAs($meo)
            ->delete(route('monitorings.evidence.destroy', [$visit, $second]))
            ->assertRedirect(route('monitorings.show', $visit));
        $this->assertDatabaseMissing('monitoring_evidence', ['id' => $second->id]);
    }

    public function test_malicious_evidence_paths_cannot_delete_or_serve_cms_media(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('cms/media/secret.jpg', 'cms-bytes');

        $officer = $this->officer();
        $visit = $this->visit($officer);
        $poisoned = MonitoringEvidence::create([
            'monitoring_id' => $visit->id,
            'file_path' => 'cms/media/secret.jpg',
            'file_type' => MonitoringEvidence::TYPE_PHOTO,
            'original_filename' => 'secret.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 9,
            'uploaded_by' => $officer->id,
        ]);
        $traversal = MonitoringEvidence::create([
            'monitoring_id' => $visit->id,
            'file_path' => 'monitorings/evidence/../cms/media/secret.jpg',
            'file_type' => MonitoringEvidence::TYPE_PHOTO,
            'original_filename' => 'secret.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 9,
            'uploaded_by' => $officer->id,
        ]);

        $admin = $this->user('Super Administrator');

        $this->actingAs($admin)
            ->get(route('monitorings.evidence.file', [$visit, $poisoned]))
            ->assertNotFound();
        $this->actingAs($admin)
            ->get(route('monitorings.evidence.file', [$visit, $traversal]))
            ->assertNotFound();

        $this->actingAs($admin)
            ->delete(route('monitorings.evidence.destroy', [$visit, $poisoned]))
            ->assertRedirect(route('monitorings.show', $visit));
        $this->actingAs($admin)
            ->delete(route('monitorings.evidence.destroy', [$visit, $traversal]))
            ->assertRedirect(route('monitorings.show', $visit));

        Storage::disk(OperationalStorage::DISK)->assertExists('cms/media/secret.jpg');
        $this->assertSame('cms-bytes', Storage::disk(OperationalStorage::DISK)->get('cms/media/secret.jpg'));
    }

    public function test_replacing_the_legacy_visit_photo_does_not_delete_evidence_files(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('monitorings/old-visit.jpg', 'old-photo');

        $officer = $this->officer();
        $visit = $this->visit($officer, ['photo' => 'monitorings/old-visit.jpg']);
        $item = $this->storedPhoto($visit, $officer, 'keep.jpg');

        $this->actingAs($officer)
            ->put(route('monitorings.update', $visit), [
                'project_id' => $visit->project_id,
                'monitoring_date' => '2026-10-18',
                'status' => 'Ongoing',
                'progress_before' => 10,
                'progress_after' => 20,
                'photo' => UploadedFile::fake()->image('replacement.jpg'),
            ])
            ->assertRedirect(route('monitorings.index'));

        $visit->refresh();
        $this->assertNotSame('monitorings/old-visit.jpg', $visit->photo);
        $this->assertTrue($this->isLegacyVisitPhotoPath((string) $visit->photo));
        Storage::disk(OperationalStorage::DISK)->assertMissing('monitorings/old-visit.jpg');
        Storage::disk(OperationalStorage::DISK)->assertExists($item->file_path);
        $this->assertDatabaseHas('monitoring_evidence', ['id' => $item->id]);
    }

    public function test_deleting_a_visit_removes_evidence_files_and_keeps_legacy_photo_compatibility(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('monitorings/visit.jpg', 'legacy');
        Storage::disk(OperationalStorage::DISK)->put('cms/media/keep.jpg', 'cms');

        $officer = $this->officer();
        $visit = $this->visit($officer, ['photo' => 'monitorings/visit.jpg']);
        $item = $this->storedPhoto($visit, $officer, 'extra.jpg');
        $admin = $this->user('Super Administrator');

        $this->actingAs($admin)
            ->get(route('monitorings.photo', $visit))
            ->assertOk();

        $this->actingAs($admin)
            ->delete(route('monitorings.destroy', $visit))
            ->assertRedirect(route('monitorings.index'));

        $this->assertDatabaseMissing('monitorings', ['id' => $visit->id]);
        $this->assertDatabaseMissing('monitoring_evidence', ['id' => $item->id]);
        Storage::disk(OperationalStorage::DISK)->assertMissing('monitorings/visit.jpg');
        Storage::disk(OperationalStorage::DISK)->assertMissing($item->file_path);
        Storage::disk(OperationalStorage::DISK)->assertExists('cms/media/keep.jpg');
    }

    public function test_reports_still_open_when_a_visit_has_evidence(): void
    {
        Storage::fake(OperationalStorage::DISK);
        $officer = $this->officer();
        $visit = $this->visit($officer);
        $this->storedPhoto($visit, $officer);
        $meo = $this->user('M&E Officer');

        $this->actingAs($meo)
            ->get(route('reports.index'))
            ->assertOk();
    }

    public function test_mismatched_visit_and_evidence_ids_are_not_found(): void
    {
        Storage::fake(OperationalStorage::DISK);
        $officer = $this->officer();
        $first = $this->visit($officer);
        $second = $this->visit($officer);
        $item = $this->storedPhoto($first, $officer);

        $this->actingAs($officer)
            ->get(route('monitorings.evidence.file', [$second, $item]))
            ->assertNotFound();
    }

    private function officer(): User
    {
        $mda = $this->ministry('Ministry of Health', 'MOH');

        return User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $mda->id,
        ]);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function ministry(string $name, string $code): Mda
    {
        return Mda::create([
            'name' => $name,
            'code' => $code,
            'status' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function visit(User $officer, array $overrides = []): Monitoring
    {
        $project = Project::create([
            'mda_id' => $officer->mda_id,
            'project_title' => 'Health Clinic '.uniqid(),
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Ongoing',
            'progress' => 10,
        ]);

        return Monitoring::create(array_merge([
            'project_id' => $project->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 0,
            'progress_after' => 10,
        ], $overrides));
    }

    private function storedPhoto(Monitoring $visit, User $uploader, string $name = 'photo.jpg'): MonitoringEvidence
    {
        $path = MonitoringEvidence::DIRECTORY.'/'.uniqid('ev_', true).'.jpg';
        Storage::disk(OperationalStorage::DISK)->put($path, 'photo-bytes');

        return MonitoringEvidence::create([
            'monitoring_id' => $visit->id,
            'file_path' => $path,
            'file_type' => MonitoringEvidence::TYPE_PHOTO,
            'original_filename' => $name,
            'mime_type' => 'image/jpeg',
            'file_size' => 11,
            'caption' => 'Stored photograph',
            'uploaded_by' => $uploader->id,
        ]);
    }

    private function mp4Upload(string $name): UploadedFile
    {
        $bytes = hex2bin('000000186674797069736f6d0000000169736f6d61766331') ?: '';
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('mp4_', true).'.mp4';
        file_put_contents($path, $bytes.str_repeat("\0", 2048));

        return new UploadedFile($path, $name, 'video/mp4', null, true);
    }

    private function isLegacyVisitPhotoPath(string $path): bool
    {
        return str_starts_with($path, 'monitorings/')
            && ! str_starts_with($path, MonitoringEvidence::DIRECTORY.'/')
            && substr_count($path, '/') === 1;
    }
}
