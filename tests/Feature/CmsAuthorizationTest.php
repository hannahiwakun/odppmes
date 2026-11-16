<?php

namespace Tests\Feature;

use App\Models\CmsEnquiry;
use App\Models\CmsMediaAsset;
use App\Models\CmsNewsArticle;
use App\Models\CmsWorkflowEvent;
use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CmsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_super_administrator_has_full_cms_access(): void
    {
        $super = $this->user('Super Administrator');
        $article = $this->article($super, 'submitted');

        $this->assertTrue($super->can('access-cms'));
        $this->assertTrue($super->can('manage-cms-users'));
        $this->assertTrue($super->can('review-cms'));
        $this->assertTrue($super->can('publish-cms'));
        $this->assertTrue($super->can('archive-cms'));
        $this->assertTrue($super->can('viewAny', CmsNewsArticle::class));
        $this->assertTrue($super->can('create', CmsNewsArticle::class));
        $this->assertTrue($super->can('update', $article));
        $this->assertTrue($super->can('approve', $article));
        $this->assertTrue($super->can('reject', $article));
        $this->assertTrue($super->can('publish', $article));
        $this->assertTrue($super->can('unpublish', $article));
        $this->assertTrue($super->can('archive', $article));
        $this->assertTrue($super->can('viewAny', CmsEnquiry::class));
        $this->assertTrue($super->can('viewAny', CmsMediaAsset::class));

        $this->actingAs($super)->get(route('cms.home'))->assertOk();
    }

    public function test_a_website_administrator_has_limited_cms_access(): void
    {
        $admin = $this->websiteAdmin();
        $ownDraft = $this->article($admin, 'draft');
        $ownSubmitted = $this->article($admin, 'submitted');
        $ownPublished = $this->article($admin, 'published');

        $this->assertTrue($admin->can('access-cms'));
        $this->assertFalse($admin->can('manage-cms-users'));
        $this->assertFalse($admin->can('review-cms'));
        $this->assertTrue($admin->can('create', CmsNewsArticle::class));
        $this->assertTrue($admin->can('update', $ownDraft));
        $this->assertTrue($admin->can('submit', $ownDraft));
        $this->assertFalse($admin->can('update', $ownSubmitted));
        $this->assertFalse($admin->can('update', $ownPublished));
        $this->assertTrue($admin->can('viewAny', CmsEnquiry::class));
        $this->assertTrue($admin->can('viewAny', CmsMediaAsset::class));
        $this->assertFalse($admin->can('update', CmsEnquiry::query()->make()));

        $this->actingAs($admin)->get(route('cms.home'))->assertOk();
    }

    public function test_other_portal_roles_are_denied_cms_access(): void
    {
        $article = $this->article($this->user('Super Administrator'), 'draft');

        foreach (['Viewer', 'M&E Officer', 'MDA Officer', 'Development Partner'] as $role) {
            $user = $this->user($role);

            $this->assertFalse($user->can('access-cms'));
            $this->assertFalse($user->can('viewAny', CmsNewsArticle::class));
            $this->assertFalse($user->can('approve', $article));

            $this->actingAs($user)->get(route('cms.home'))->assertForbidden();
            $this->actingAs($user)
                ->post(route('cms.workflow.approve', ['type' => 'news', 'id' => $article->id]))
                ->assertForbidden();
        }
    }

    public function test_a_website_administrator_cannot_approve_content_over_http(): void
    {
        $admin = $this->websiteAdmin();
        $article = $this->article($admin, 'submitted');

        $this->assertFalse($admin->can('approve', $article));

        $this->actingAs($admin)
            ->from(route('cms.home'))
            ->post(route('cms.workflow.approve', ['type' => 'news', 'id' => $article->id]), [
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'approved_by' => $admin->id,
            ])
            ->assertForbidden();

        $this->assertSame('submitted', $article->fresh()->status);
        $this->assertNull($article->fresh()->reviewed_by);
        $this->assertSame(0, CmsWorkflowEvent::query()->count());
    }

    public function test_a_website_administrator_cannot_reject_content_over_http(): void
    {
        $admin = $this->websiteAdmin();
        $article = $this->article($admin, 'submitted');

        $this->assertFalse($admin->can('reject', $article));

        $this->actingAs($admin)
            ->from(route('cms.home'))
            ->post(route('cms.workflow.reject', ['type' => 'news', 'id' => $article->id]), [
                'reason' => 'Trying to reject my own work',
                'status' => 'rejected',
            ])
            ->assertForbidden();

        $this->assertSame('submitted', $article->fresh()->status);
        $this->assertNull($article->fresh()->rejected_reason);
    }

    public function test_a_website_administrator_cannot_publish_unpublish_or_archive_content(): void
    {
        $admin = $this->websiteAdmin();
        $approved = $this->article($admin, 'approved');
        $published = $this->article($admin, 'published');

        $this->assertFalse($admin->can('publish', $approved));
        $this->assertFalse($admin->can('unpublish', $published));
        $this->assertFalse($admin->can('archive', $published));

        $this->actingAs($admin);

        $this->post(route('cms.workflow.publish', ['type' => 'news', 'id' => $approved->id]))
            ->assertForbidden();
        $this->post(route('cms.workflow.unpublish', ['type' => 'news', 'id' => $published->id]))
            ->assertForbidden();
        $this->post(route('cms.workflow.archive', ['type' => 'news', 'id' => $published->id]))
            ->assertForbidden();

        $this->assertSame('approved', $approved->fresh()->status);
        $this->assertSame('published', $published->fresh()->status);
        $this->assertNull($published->fresh()->archived_by);
    }

    public function test_a_super_administrator_can_approve_and_reject_submissions_including_their_own(): void
    {
        $super = $this->user('Super Administrator');
        $own = $this->article($super, 'submitted');
        $other = $this->article($this->websiteAdmin(), 'submitted');

        $this->actingAs($super)
            ->from(route('cms.home'))
            ->post(route('cms.workflow.approve', ['type' => 'news', 'id' => $own->id]))
            ->assertRedirect(route('cms.home'));

        $own = $own->fresh();
        $this->assertSame('approved', $own->status);
        $this->assertSame($super->id, $own->reviewed_by);
        $this->assertNotNull($own->reviewed_at);
        $this->assertDatabaseHas('cms_workflow_events', [
            'content_type' => 'news',
            'content_id' => $own->id,
            'from_status' => 'submitted',
            'to_status' => 'approved',
            'actor_id' => $super->id,
        ]);

        $this->actingAs($super)
            ->from(route('cms.home'))
            ->post(route('cms.workflow.reject', ['type' => 'news', 'id' => $other->id]), [
                'reason' => 'Please correct the headline.',
            ])
            ->assertRedirect(route('cms.home'));

        $other = $other->fresh();
        $this->assertSame('rejected', $other->status);
        $this->assertSame($super->id, $other->reviewed_by);
        $this->assertSame('Please correct the headline.', $other->rejected_reason);
        $this->assertNotNull($other->reviewed_at);
    }

    public function test_rejection_requires_a_reason(): void
    {
        $super = $this->user('Super Administrator');
        $article = $this->article($this->websiteAdmin(), 'submitted');

        $this->actingAs($super)
            ->from(route('cms.home'))
            ->post(route('cms.workflow.reject', ['type' => 'news', 'id' => $article->id]), [
                'reason' => '',
            ])
            ->assertRedirect(route('cms.home'))
            ->assertSessionHasErrors('reason');

        $this->assertSame('submitted', $article->fresh()->status);
        $this->assertNull($article->fresh()->rejected_reason);
        $this->assertSame(0, CmsWorkflowEvent::query()->count());
    }

    public function test_protected_workflow_fields_cannot_be_mass_assigned(): void
    {
        $user = $this->websiteAdmin();
        $article = $this->article($user, 'draft');

        $article->fill([
            'title' => 'Updated title',
            'status' => 'published',
            'created_by' => $user->id,
            'reviewed_by' => $user->id,
            'approved_by' => $user->id,
            'published_by' => $user->id,
            'archived_by' => $user->id,
            'rejected_reason' => 'injected',
        ]);
        $article->save();

        $article = $article->fresh();
        $this->assertSame('Updated title', $article->title);
        $this->assertSame('draft', $article->status);
        $this->assertNull($article->reviewed_by);
        $this->assertNull($article->published_by);
        $this->assertNull($article->archived_by);
        $this->assertNull($article->rejected_reason);

        $created = CmsNewsArticle::create([
            'slug' => 'mass-assign-create',
            'title' => 'Created',
            'excerpt' => 'Excerpt',
            'body' => 'Body',
            'status' => 'published',
            'published_by' => $user->id,
        ]);

        $this->assertSame('draft', $created->status);
        $this->assertNull($created->published_by);

        $enquiry = new CmsEnquiry;
        try {
            $enquiry->fill([
                'status' => 'closed',
                'email' => 'hidden@example.test',
                'message' => 'secret',
                'assigned_to' => $user->id,
            ]);
            $this->fail('Enquiry attributes must not be mass assignable.');
        } catch (MassAssignmentException) {
            $this->assertNull($enquiry->status);
            $this->assertNull($enquiry->email);
            $this->assertNull($enquiry->assigned_to);
        }

        $media = new CmsMediaAsset;
        $media->fill([
            'original_name' => 'photo.jpg',
            'is_cleared_for_public' => true,
            'disk' => 'public',
            'path' => 'secret/path.jpg',
            'uploaded_by' => $user->id,
        ]);
        $this->assertSame('photo.jpg', $media->original_name);
        $this->assertFalse((bool) $media->is_cleared_for_public);
        $this->assertNull($media->disk);
        $this->assertNull($media->path);
        $this->assertNull($media->uploaded_by);
    }

    public function test_mda_and_development_partner_project_restrictions_remain_intact(): void
    {
        [$health, $works] = $this->ministries();
        $alpha = DevelopmentPartner::create([
            'partner_name' => 'Alpha Fund',
            'partner_type' => 'Bilateral',
        ]);
        $beta = DevelopmentPartner::create([
            'partner_name' => 'Beta Fund',
            'partner_type' => 'Bilateral',
        ]);

        $own = $this->project($health, 'Health Clinic', $alpha);
        $other = $this->project($works, 'Rural Road', $beta);

        $officer = $this->user('MDA Officer', ['mda_id' => $health->id]);
        $partner = $this->user('Development Partner', ['development_partner_id' => $alpha->id]);
        $websiteAdmin = $this->websiteAdmin();

        $this->assertTrue($officer->canAccessProject($own));
        $this->assertFalse($officer->canAccessProject($other));
        $this->assertTrue($partner->canAccessProject($own));
        $this->assertFalse($partner->canAccessProject($other));
        $this->assertFalse($websiteAdmin->canAccessProject($own));
        $this->assertFalse($websiteAdmin->can('view', $own));

        $this->assertSame([$own->id], Project::query()->visibleTo($officer)->pluck('id')->all());
        $this->assertSame([$own->id], Project::query()->visibleTo($partner)->pluck('id')->all());
        $this->assertSame([], Project::query()->visibleTo($websiteAdmin)->pluck('id')->all());

        $this->actingAs($officer)->get(route('projects.show', $other))->assertNotFound();
        $this->actingAs($officer)->get(route('projects.show', $own))->assertOk();
        $this->actingAs($websiteAdmin)->get(route('projects.show', $own))->assertForbidden();
        $this->actingAs($websiteAdmin)->get(route('monitorings.index'))->assertForbidden();
        $this->actingAs($websiteAdmin)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($websiteAdmin)->get(route('users.index'))->assertForbidden();
    }

    public function test_a_website_administrator_can_submit_owned_drafts_but_not_others(): void
    {
        $admin = $this->websiteAdmin();
        $other = $this->websiteAdmin();
        $own = $this->article($admin, 'draft');
        $foreign = $this->article($other, 'draft');

        $this->actingAs($admin)
            ->from(route('cms.home'))
            ->post(route('cms.workflow.submit', ['type' => 'news', 'id' => $own->id]))
            ->assertRedirect(route('cms.home'));

        $this->assertSame('submitted', $own->fresh()->status);
        $this->assertSame($admin->id, $own->fresh()->submitted_by);

        $this->actingAs($admin)
            ->post(route('cms.workflow.submit', ['type' => 'news', 'id' => $foreign->id]))
            ->assertForbidden();

        $this->assertSame('draft', $foreign->fresh()->status);
    }

    private function websiteAdmin(): User
    {
        return $this->user('DPMO Website Administrator');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function user(string $role, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
        ], $overrides));
    }

    private function article(User $owner, string $status): CmsNewsArticle
    {
        $article = CmsNewsArticle::create([
            'slug' => 'article-'.Str::lower(Str::random(12)),
            'title' => 'Article title',
            'excerpt' => 'Short excerpt for the article.',
            'body' => 'Full article body.',
        ]);

        $article->created_by = $owner->id;
        $article->status = $status;
        $article->save();

        return $article->fresh();
    }

    /**
     * @return array{0: Mda, 1: Mda}
     */
    private function ministries(): array
    {
        return [
            Mda::create([
                'name' => 'Ministry of Health',
                'code' => 'MOH'.substr(md5(uniqid('', true)), 0, 4),
                'status' => true,
            ]),
            Mda::create([
                'name' => 'Ministry of Works',
                'code' => 'MOW'.substr(md5(uniqid('', true)), 0, 4),
                'status' => true,
            ]),
        ];
    }

    private function project(Mda $mda, string $title, ?DevelopmentPartner $partner = null): Project
    {
        return Project::create([
            'mda_id' => $mda->id,
            'development_partner_id' => $partner?->id,
            'project_title' => $title,
            'sector' => 'Education',
            'lga' => 'Akure North',
            'status' => 'Planning',
            'progress' => 0,
        ]);
    }
}
