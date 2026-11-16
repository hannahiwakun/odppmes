<?php

namespace Tests\Feature;

use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportAccuracyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_keeps_budget_status_and_separates_low_progress_from_register_delay(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $health = $this->ministry('Ministry of Health', 'MOH');

        $this->project($health, 'Planning Clinic', [
            'status' => 'Planning',
            'progress' => 10,
            'budget' => 1000,
            'approved_value' => 800000.50,
        ]);
        $this->project($health, 'On Track Road', [
            'status' => 'Ongoing',
            'progress' => 80,
            'budget' => 2000,
            'initiative_status' => 'Delayed',
        ]);
        $this->project($health, 'Attention School', [
            'status' => 'Ongoing',
            'progress' => 40,
            'budget' => 500,
        ]);
        $this->project($health, 'Suspended Bridge', [
            'status' => 'Suspended',
            'progress' => 29,
            'budget' => 0,
        ]);
        $this->project($health, 'Finished Hospital', [
            'status' => 'Completed',
            'progress' => 100,
            'budget' => 3000,
            'initiative_status' => 'Delayed',
        ]);
        $this->project($health, 'Cancelled Market', [
            'status' => 'Cancelled',
            'progress' => 0,
            'budget' => 4000,
            'initiative_status' => 'Delayed',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Total Project Budget');
        $response->assertSee('10,500.00');
        $response->assertDontSee('800,000.50');
        $response->assertDontSee('Delayed Projects');
        $response->assertSee('MDAs (statewide)');
        $response->assertSee('Development Partners (statewide)');
        $response->assertSee('Top Implementing MDAs');
        $response->assertSee('Development Partner Summary');
        $response->assertSeeInOrder([
            'On Track Projects',
            '1',
            'Needs Attention',
            '1',
            'Low Progress (Below 30%)',
            '2',
            'Register Status: Delayed',
            '3',
        ]);
        $response->assertSee('A monitoring visit may update the project status separately.');
        $response->assertSeeInOrder([
            'Planning',
            'Ongoing',
            'Completed',
            'Suspended',
            'Cancelled',
        ]);
        $this->assertMatchesRegularExpression(
            '/data:\s*\[\s*1,\s*2,\s*1,\s*1,\s*1\s*\]/s',
            $response->getContent()
        );
        $response->assertSee('43.2%');
    }

    public function test_an_mda_officer_sees_only_their_ministry_projects_and_a_scoped_report(): void
    {
        $health = $this->ministry('Ministry of Health', 'MOH');
        $works = $this->ministry('Ministry of Works', 'MOW');
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $health->id,
            'name' => 'Health Desk Officer',
        ]);
        $otherOfficer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $works->id,
            'name' => 'Works Desk Officer',
        ]);
        $partner = User::factory()->create(['role' => 'Development Partner']);
        $worksPartner = DevelopmentPartner::create([
            'partner_name' => 'Works Only Partner',
            'partner_type' => 'Bilateral',
            'status' => true,
        ]);

        $own = $this->project($health, 'Health Clinic', [
            'budget' => 1234.56,
            'status' => 'Ongoing',
            'progress' => 50,
            'sector' => 'Health',
        ]);
        $other = $this->project($works, 'Rural Road', [
            'budget' => 987654.32,
            'status' => 'Ongoing',
            'progress' => 50,
            'sector' => 'Infrastructure',
            'development_partner_id' => $worksPartner->id,
        ]);
        Monitoring::create([
            'project_id' => $own->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 20,
            'progress_after' => 50,
        ]);
        Monitoring::create([
            'project_id' => $other->id,
            'user_id' => $otherOfficer->id,
            'monitoring_date' => '2026-10-18',
            'status' => 'Ongoing',
            'progress_before' => 10,
            'progress_after' => 50,
        ]);

        $dashboard = $this->actingAs($officer)->get(route('dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee('My MDA Dashboard');
        $dashboard->assertSee('Project monitoring for Ministry of Health.');
        $dashboard->assertSee('1,234.56');
        $dashboard->assertDontSee('987,654.32');
        $dashboard->assertSee('Health Clinic');
        $dashboard->assertSee('Health Desk Officer');
        $dashboard->assertDontSee('Rural Road');
        $dashboard->assertDontSee('Works Desk Officer');
        $dashboard->assertDontSee('Ministry of Works');
        $dashboard->assertDontSee('Works Only Partner');
        $dashboard->assertDontSee('MDAs (statewide)');
        $dashboard->assertDontSee('Development Partners (statewide)');
        $dashboard->assertDontSee('Top Implementing MDAs');
        $dashboard->assertDontSee('Development Partner Summary');
        $html = $dashboard->getContent();
        $this->assertStringNotContainsString('Infrastructure', $html);
        $this->assertMatchesRegularExpression('/data:\s*\[\s*0,\s*1,\s*0,\s*0,\s*0\s*\]/s', $html);

        $this->actingAs($officer)->get(route('projects.show', $other))->assertNotFound();
        $this->actingAs($officer)->get(route('monitorings.show', Monitoring::query()->where('project_id', $other->id)->first()))->assertNotFound();

        $this->actingAs($officer)
            ->get(route('reports.index', ['mda_id' => $works->id]))
            ->assertOk()
            ->assertSee('Health Clinic')
            ->assertDontSee('Rural Road');
        $this->actingAs($officer)->get(route('reports.pdf'))->assertOk();
        $this->actingAs($partner)->get(route('reports.pdf'))->assertForbidden();
    }

    public function test_pdf_report_uses_the_screen_filters_columns_and_latest_order(): void
    {
        $viewer = User::factory()->create(['role' => 'Viewer']);
        $health = $this->ministry('Ministry of Health', 'MOH');
        $works = $this->ministry('Ministry of Works', 'MOW');
        $partner = DevelopmentPartner::create([
            'partner_name' => 'World Health Partner',
            'partner_type' => 'Multilateral',
            'status' => true,
        ]);

        $older = $this->project($health, 'Older Clinic', [
            'status' => 'Completed',
            'progress' => 100,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'development_partner_id' => $partner->id,
            'approved_value' => 800000.50,
            'budget' => 1500,
        ]);
        $newer = $this->project($health, 'Newer Clinic', [
            'status' => 'Completed',
            'progress' => 90,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'development_partner_id' => $partner->id,
            'budget' => 2500,
        ]);
        $this->project($works, 'Rural Road', [
            'status' => 'Ongoing',
            'progress' => 20,
            'sector' => 'Infrastructure',
            'lga' => 'Ondo West',
            'approved_value' => 700000,
        ]);

        $older->forceFill(['created_at' => '2026-10-01 09:00:00'])->save();
        $newer->forceFill(['created_at' => '2026-10-16 09:00:00'])->save();

        $filters = [
            'mda_id' => $health->id,
            'partner_id' => $partner->id,
            'status' => 'Completed',
            'sector' => 'Health',
            'lga' => 'Akure South',
        ];

        $screen = $this->actingAs($viewer)->get(route('reports.index', $filters));
        $screen->assertOk();
        $screen->assertSeeInOrder(['Newer Clinic', 'Older Clinic']);
        $screen->assertDontSee('Rural Road');
        $screen->assertSee('Projects Found');
        $screen->assertSee('Average Progress');
        $screen->assertSee('Completed Projects');

        $pdf = $this->actingAs($viewer)->get(route('reports.pdf', $filters));
        $pdf->assertOk();
        $pdf->assertHeader('content-type', 'application/pdf');

        $text = $this->pdfText($pdf->getContent());

        $this->assertStringContainsString('Ondo State Government', $text);
        $this->assertStringContainsString('ODPPMES', $text);
        $this->assertStringContainsString('Project Reports', $text);
        $this->assertStringContainsString('Projects Found', $text);
        $this->assertStringContainsString('Average Progress', $text);
        $this->assertStringContainsString('Completed Projects', $text);
        $this->assertStringContainsString('Project', $text);
        $this->assertStringContainsString('MDA', $text);
        $this->assertStringContainsString('Partner', $text);
        $this->assertStringContainsString('Sector', $text);
        $this->assertStringContainsString('LGA', $text);
        $this->assertStringContainsString('Status', $text);
        $this->assertStringContainsString('Progress', $text);
        $this->assertStringContainsString('Ministry of Health', $text);
        $this->assertStringContainsString('World Health Partner', $text);
        $this->assertStringContainsString('Akure South', $text);
        $this->assertStringContainsString('Newer Clinic', $text);
        $this->assertStringContainsString('Older Clinic', $text);
        $this->assertTrue(strpos($text, 'Newer Clinic') < strpos($text, 'Older Clinic'));
        $this->assertStringNotContainsString('Rural Road', $text);
        $this->assertStringNotContainsString('800,000.50', $text);
        $this->assertStringNotContainsString('approved_value', $text);
        $this->assertStringNotContainsString('View', $text);
    }

    private function ministry(string $name, string $code): Mda
    {
        return Mda::create([
            'name' => $name,
            'code' => $code,
            'status' => true,
        ]);
    }

    private function project(Mda $mda, string $title, array $overrides = []): Project
    {
        return Project::create(array_merge([
            'mda_id' => $mda->id,
            'project_title' => $title,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Planning',
            'progress' => 0,
            'budget' => 0,
        ], $overrides));
    }

    private function pdfText(string $binary): string
    {
        $text = $binary;

        if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $binary, $matches)) {
            foreach ($matches[1] as $stream) {
                $decoded = @gzuncompress($stream);

                if ($decoded === false) {
                    $decoded = @gzinflate($stream);
                }

                if (is_string($decoded)) {
                    $text .= "\n".$decoded;
                }
            }
        }

        return $text;
    }
}
