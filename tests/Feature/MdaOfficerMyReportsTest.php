<?php

namespace Tests\Feature;

use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MdaOfficerMyReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_mda_officer_sees_only_their_malaria_project_and_updated_progress(): void
    {
        [$health, $works, $officer] = $this->healthOfficerSetup();
        $who = $this->partner('World Health Partner');
        $worksPartner = $this->partner('Works Only Partner');

        $this->actingAs($officer)
            ->post(route('projects.store'), [
                'project_title' => 'Malaria Control Programme',
                'mda_id' => $works->id,
                'development_partner_id' => $who->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Ongoing',
                'progress' => 40,
            ])
            ->assertRedirect(route('projects.index'));

        $this->project($works, 'Rural Road', [
            'status' => 'Ongoing',
            'progress' => 80,
            'sector' => 'Infrastructure',
            'lga' => 'Ondo West',
            'development_partner_id' => $worksPartner->id,
        ]);
        $this->project($health, 'Completed Clinic', [
            'status' => 'Completed',
            'progress' => 100,
            'sector' => 'Health',
            'lga' => 'Owo',
        ]);

        $malaria = Project::query()->where('project_title', 'Malaria Control Programme')->firstOrFail();
        $this->assertSame($health->id, $malaria->mda_id);

        $report = $this->actingAs($officer)->get(route('reports.index'));
        $report->assertOk();
        $report->assertSee('My Reports', false);
        $report->assertSee('Ministry of Health');
        $report->assertSee('Malaria Control Programme');
        $report->assertSee('40%');
        $report->assertSee('Completed Clinic');
        $report->assertDontSee('Rural Road');
        $report->assertDontSee('Works Only Partner');
        $report->assertDontSee('name="mda_id"', false);
        $report->assertSee('World Health Partner');
        $report->assertSee('70%', false);
        $this->assertSame(2, Project::query()->where('mda_id', $health->id)->count());

        $this->actingAs($officer)
            ->put(route('projects.update', $malaria), [
                'project_title' => 'Malaria Control Programme',
                'mda_id' => $works->id,
                'development_partner_id' => $who->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Ongoing',
                'progress' => 72,
            ])
            ->assertRedirect(route('projects.index'));

        $updated = $this->actingAs($officer)->get(route('reports.index'));
        $updated->assertSee('Malaria Control Programme');
        $updated->assertSee('72%');
        $updated->assertDontSee('40%');
        $this->assertSame(72, (int) $malaria->fresh()->progress);
    }

    public function test_filters_and_reset_stay_inside_the_assigned_mda(): void
    {
        [$health, $works, $officer] = $this->healthOfficerSetup();
        $who = $this->partner('World Health Partner');
        $worksPartner = $this->partner('Works Only Partner');

        $this->project($health, 'Malaria Control Programme', [
            'status' => 'Ongoing',
            'progress' => 72,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'development_partner_id' => $who->id,
        ]);
        $this->project($health, 'Completed Clinic', [
            'status' => 'Completed',
            'progress' => 100,
            'sector' => 'Health',
            'lga' => 'Owo',
        ]);
        $this->project($works, 'Rural Road', [
            'status' => 'Ongoing',
            'progress' => 80,
            'sector' => 'Infrastructure',
            'lga' => 'Ondo West',
            'development_partner_id' => $worksPartner->id,
        ]);

        $filtered = $this->actingAs($officer)->get(route('reports.index', [
            'mda_id' => $works->id,
            'partner_id' => $who->id,
            'status' => 'Ongoing',
            'sector' => 'Health',
            'lga' => 'Akure South',
        ]));
        $filtered->assertOk();
        $filtered->assertSee('Malaria Control Programme');
        $filtered->assertDontSee('Completed Clinic');
        $filtered->assertDontSee('Rural Road');
        $filtered->assertSee('Filter Report', false);
        $filtered->assertSee('Reset', false);

        $wrongPartner = $this->actingAs($officer)->get(route('reports.index', [
            'partner_id' => $worksPartner->id,
        ]));
        $wrongPartner->assertOk();
        $wrongPartner->assertDontSee('Rural Road');
        $wrongPartner->assertDontSee('Malaria Control Programme');

        $reset = $this->actingAs($officer)->get(route('reports.index'));
        $reset->assertSee('Malaria Control Programme');
        $reset->assertSee('Completed Clinic');
        $reset->assertDontSee('Rural Road');
        $reset->assertSee('Ministry of Health');
    }

    public function test_pdf_export_contains_only_the_assigned_mda_and_heading(): void
    {
        [$health, $works, $officer] = $this->healthOfficerSetup();
        $who = $this->partner('World Health Partner');

        $this->project($health, 'Malaria Control Programme', [
            'status' => 'Ongoing',
            'progress' => 72,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'development_partner_id' => $who->id,
        ]);
        $this->project($works, 'Rural Road', [
            'status' => 'Ongoing',
            'progress' => 80,
            'sector' => 'Infrastructure',
            'lga' => 'Ondo West',
        ]);

        $pdf = $this->actingAs($officer)->get(route('reports.pdf', [
            'mda_id' => $works->id,
            'status' => 'Ongoing',
        ]));
        $pdf->assertOk();
        $pdf->assertHeader('content-type', 'application/pdf');
        $pdf->assertHeader('content-disposition', 'attachment; filename=ODPPMES_My_Reports.pdf');

        $text = $this->pdfText($pdf->getContent());
        $this->assertStringContainsString('Ondo State Government', $text);
        $this->assertStringContainsString('ODPPMES', $text);
        $this->assertStringContainsString('My Reports', $text);
        $this->assertStringContainsString('Ministry of Health', $text);
        $this->assertStringContainsString('Malaria Control Programme', $text);
        $this->assertStringContainsString('World Health Partner', $text);
        $this->assertStringContainsString('Akure South', $text);
        $this->assertStringContainsString('72%', $text);
        $this->assertStringNotContainsString('Rural Road', $text);
        $this->assertStringNotContainsString('Ministry of Works', $text);
    }

    public function test_other_roles_keep_their_report_permissions(): void
    {
        [$health, $works] = [$this->ministry('Ministry of Health', 'MOH'), $this->ministry('Ministry of Works', 'MOW')];
        $this->project($health, 'Malaria Control Programme');
        $this->project($works, 'Rural Road');

        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $meo = User::factory()->create(['role' => 'M&E Officer']);
        $viewer = User::factory()->create(['role' => 'Viewer']);
        $partner = User::factory()->create(['role' => 'Development Partner']);

        foreach ([$admin, $meo, $viewer] as $user) {
            $this->actingAs($user)
                ->get(route('reports.index'))
                ->assertOk()
                ->assertSee('Malaria Control Programme')
                ->assertSee('Rural Road')
                ->assertSee('Project Reports');
        }

        $this->actingAs($partner)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($partner)->get(route('reports.pdf'))->assertForbidden();
    }

    /**
     * @return array{0: Mda, 1: Mda, 2: User}
     */
    private function healthOfficerSetup(): array
    {
        $health = $this->ministry('Ministry of Health', 'MOH');
        $works = $this->ministry('Ministry of Works', 'MOW');
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $health->id,
            'name' => 'Health Desk Officer',
        ]);

        return [$health, $works, $officer];
    }

    private function ministry(string $name, string $code): Mda
    {
        return Mda::create([
            'name' => $name,
            'code' => $code,
            'status' => true,
        ]);
    }

    private function partner(string $name): DevelopmentPartner
    {
        return DevelopmentPartner::create([
            'partner_name' => $name,
            'partner_type' => 'Multilateral',
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
