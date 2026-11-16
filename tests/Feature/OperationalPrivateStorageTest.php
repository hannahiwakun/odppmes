<?php

namespace Tests\Feature;

use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectPhoto;
use App\Models\User;
use App\Support\OperationalStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperationalPrivateStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_operational_upload_is_not_stored_on_the_public_disk(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::fake('public');

        $ministry = Mda::create([
            'name' => 'Ministry of Health',
            'code' => 'MOH',
            'status' => true,
        ]);
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $ministry->id,
        ]);
        $project = Project::create([
            'mda_id' => $ministry->id,
            'project_title' => 'Health Clinic',
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Planning',
            'progress' => 0,
        ]);

        $this->actingAs($officer)
            ->post(route('projects.photos.store', $project), [
                'photo' => UploadedFile::fake()->image('clinic.jpg'),
                'caption' => 'Clinic front',
            ])
            ->assertRedirect(route('projects.photos.index', $project));

        $photo = ProjectPhoto::firstOrFail();
        $this->assertStringStartsWith('project_photos/', $photo->photo);
        Storage::disk(OperationalStorage::DISK)->assertExists($photo->photo);
        Storage::disk('public')->assertMissing($photo->photo);

        $this->actingAs($officer)
            ->post(route('project-documents.store', $project), [
                'title' => 'Clinic plan',
                'document_type' => 'Report',
                'document' => UploadedFile::fake()->image('plan.jpg'),
            ])
            ->assertRedirect(route('project-documents.index', $project));

        $document = ProjectDocument::firstOrFail();
        $this->assertStringStartsWith('project_documents/', $document->file_path);
        Storage::disk(OperationalStorage::DISK)->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);

        $this->actingAs($officer)
            ->post(route('monitorings.store'), [
                'project_id' => $project->id,
                'monitoring_date' => '2026-11-15',
                'status' => 'Ongoing',
                'progress_before' => 0,
                'progress_after' => 10,
                'photo' => UploadedFile::fake()->image('visit.jpg'),
            ])
            ->assertRedirect(route('monitorings.index'));

        $visit = Monitoring::firstOrFail();
        $this->assertNotNull($visit->photo);
        $this->assertStringStartsWith('monitorings/', $visit->photo);
        Storage::disk(OperationalStorage::DISK)->assertExists($visit->photo);
        Storage::disk('public')->assertMissing($visit->photo);
    }
}
