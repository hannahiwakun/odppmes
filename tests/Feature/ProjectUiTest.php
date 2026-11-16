<?php

namespace Tests\Feature;

use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_uses_save_project_and_keeps_validation_input(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $health = $this->ministry('Ministry of Health', 'MOHUI');
        $works = $this->ministry('Ministry of Works', 'MOWUI');
        $partner = DevelopmentPartner::create([
            'partner_name' => 'World Health Partner',
            'partner_type' => 'UN Agency',
            'status' => true,
        ]);

        $page = $this->actingAs($admin)->get(route('projects.create'));

        $page->assertOk()
            ->assertSee('Save Project')
            ->assertDontSee('Update Monitoring')
            ->assertSee('Development register')
            ->assertSee('Total approved value')
            ->assertSee('name="coordination_form"', false)
            ->assertSee('for="project-title"', false)
            ->assertDontSee('name="budget"', false)
            ->assertDontSee('name="progress"', false)
            ->assertDontSee('name="initiative_status"', false);

        $this->actingAs($admin)
            ->from(route('projects.create'))
            ->post(route('projects.store'), [
                'project_title' => 'Kept Clinic',
                'project_code' => 'ODP-9',
                'mda_id' => $health->id,
                'development_partner_id' => $partner->id,
                'sector' => '',
                'lga' => 'Akure South',
                'nature' => 'Grant',
                'verification_status' => 'Verified',
                'participating_mda_ids' => [$works->id],
                'coordination_form' => '1',
            ])
            ->assertRedirect(route('projects.create'))
            ->assertSessionHasErrors('sector');

        $this->actingAs($admin)
            ->get(route('projects.create'))
            ->assertOk()
            ->assertSee('value="Kept Clinic"', false)
            ->assertSee('value="ODP-9"', false)
            ->assertSee('value="Akure South"', false)
            ->assertSee('value="Grant"', false)
            ->assertSee('value="'.$health->id.'" selected', false)
            ->assertSee('value="'.$partner->id.'" selected', false)
            ->assertSee('value="'.$works->id.'" selected', false)
            ->assertSee('value="Verified" selected', false);
    }

    public function test_edit_page_shows_saved_project_fields(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $mda = $this->ministry('Ministry of Health', 'MOHED');

        $project = Project::create([
            'mda_id' => $mda->id,
            'project_title' => 'Original Title',
            'project_code' => 'ODP-100',
            'sector' => 'Health',
            'lga' => 'Akure South',
            'ward' => 'Ward 4',
            'budget' => 1500,
            'status' => 'Ongoing',
            'initiative_status' => 'Delayed',
            'progress' => 40,
            'description' => 'Keep this description.',
        ]);

        $this->actingAs($admin)
            ->get(route('projects.edit', $project))
            ->assertOk()
            ->assertSee('Original Title')
            ->assertSee('ODP-100')
            ->assertSee('Ward 4')
            ->assertSee('1500')
            ->assertSee('Keep this description.')
            ->assertSee('Register Status')
            ->assertSee('Saving a register status also updates the current project status. Delayed is recorded as Ongoing for the current status.')
            ->assertSee('name="initiative_status"', false)
            ->assertSee('value="Delayed" selected', false)
            ->assertSee('Update Project');
    }

    public function test_an_mda_officer_cannot_open_another_ministrys_project(): void
    {
        $health = $this->ministry('Ministry of Health', 'MOH404');
        $works = $this->ministry('Ministry of Works', 'MOW404');
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $health->id,
        ]);
        $other = Project::create([
            'mda_id' => $works->id,
            'project_title' => 'Rural Road',
            'sector' => 'Infrastructure',
            'lga' => 'Ondo West',
            'status' => 'Planning',
            'progress' => 0,
        ]);

        $this->actingAs($officer)->get(route('projects.show', $other))->assertNotFound();
        $this->actingAs($officer)->get(route('projects.edit', $other))->assertNotFound();
    }

    public function test_photo_and_document_pages_keep_role_specific_delete_confirmation(): void
    {
        $mda = $this->ministry('Ministry of Health', 'MOHEV');
        $project = Project::create([
            'mda_id' => $mda->id,
            'project_title' => 'Clinic Support',
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Ongoing',
            'initiative_status' => 'Delayed',
            'progress' => 25,
            'budget' => 1500,
            'approved_value' => 800000.50,
        ]);
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $mda->id,
        ]);
        $meo = User::factory()->create(['role' => 'M&E Officer']);

        $captioned = ProjectPhoto::create([
            'project_id' => $project->id,
            'photo' => 'project_photos/roof.jpg',
            'caption' => 'Clinic roof',
            'uploaded_by' => $meo->id,
        ]);
        ProjectPhoto::create([
            'project_id' => $project->id,
            'photo' => 'project_photos/plain.jpg',
            'caption' => null,
            'uploaded_by' => $meo->id,
        ]);
        ProjectDocument::create([
            'project_id' => $project->id,
            'title' => 'Signed Agreement',
            'document_type' => 'Agreement',
            'file_name' => 'agreement.pdf',
            'file_path' => 'project_documents/agreement.pdf',
            'uploaded_by' => $meo->id,
        ]);

        $this->actingAs($officer)
            ->get(route('projects.photos.index', $project))
            ->assertOk()
            ->assertDontSee('Delete this photo')
            ->assertSee('alt="Clinic roof"', false)
            ->assertSee('alt="Project photo"', false)
            ->assertSee(route('projects.photos.file', [$project, $captioned]), false)
            ->assertDontSee('/storage/project_photos', false);

        $this->actingAs($officer)
            ->get(route('project-documents.index', $project))
            ->assertOk()
            ->assertDontSee('Are you sure you want to delete this document?')
            ->assertSee('Download');

        $this->actingAs($meo)
            ->get(route('projects.photos.index', $project))
            ->assertOk()
            ->assertSee('Delete this photo');

        $this->actingAs($meo)
            ->get(route('project-documents.index', $project))
            ->assertOk()
            ->assertSee('Are you sure you want to delete this document?');

        $this->actingAs($meo)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Clinic Support')
            ->assertSee('Register: Delayed')
            ->assertSee('Development projects recorded for Ondo State.');

        $this->actingAs($meo)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Project budget')
            ->assertSee('This is the project budget field.')
            ->assertSee('1,500.00')
            ->assertSee('800,000.50')
            ->assertSee('Register status: Delayed');

        $this->actingAs($meo)
            ->from(route('project-documents.create', $project))
            ->post(route('project-documents.store', $project), [
                'title' => 'Quarterly Report',
                'document_type' => 'Agreement',
                'remarks' => 'Signed copy',
            ])
            ->assertRedirect(route('project-documents.create', $project))
            ->assertSessionHasErrors('document');

        $this->actingAs($meo)
            ->get(route('project-documents.create', $project))
            ->assertOk()
            ->assertSee('Upload Document')
            ->assertSee('Maximum size: 10MB.')
            ->assertSee('value="Quarterly Report"', false)
            ->assertSee('value="Agreement" selected', false)
            ->assertSee('Signed copy')
            ->assertDontSee('bg-indigo-600', false);
    }

    private function ministry(string $name, string $code): Mda
    {
        return Mda::create([
            'name' => $name,
            'code' => $code,
            'status' => true,
        ]);
    }
}
