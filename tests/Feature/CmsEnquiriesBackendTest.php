<?php

namespace Tests\Feature;

use App\Models\CmsEnquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CmsEnquiriesBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_roles_can_view_enquiries_and_only_super_admin_can_manage_them(): void
    {
        $enquiry = $this->enquiry();
        $admin = $this->user('DPMO Website Administrator');
        $super = $this->user('Super Administrator');

        $this->actingAs($admin)
            ->get(route('cms.enquiries.index'))
            ->assertOk()
            ->assertSee($enquiry->subject, false)
            ->assertSee($enquiry->email, false)
            ->assertSee($enquiry->name, false);

        $this->actingAs($admin)
            ->get(route('cms.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee($enquiry->message, false);

        $this->actingAs($admin)
            ->patch(route('cms.enquiries.update', $enquiry), [
                'status' => CmsEnquiry::STATUS_CLOSED,
            ])
            ->assertForbidden();

        $this->actingAs($super)
            ->patch(route('cms.enquiries.update', $enquiry), [
                'status' => CmsEnquiry::STATUS_CLOSED,
                'internal_notes' => 'Handled in office.',
            ])
            ->assertRedirect(route('cms.enquiries.show', $enquiry));

        $this->assertSame(CmsEnquiry::STATUS_CLOSED, $enquiry->fresh()->status);
        $this->assertNotNull($enquiry->fresh()->handled_at);
    }

    public function test_enquiries_are_not_public_and_pii_is_not_written_to_application_logs(): void
    {
        Log::spy();
        $enquiry = $this->enquiry();

        $this->get(route('public.contact'))->assertOk()->assertDontSee($enquiry->email, false);
        $this->get('/cms/enquiries/'.$enquiry->id)->assertRedirect();

        $this->actingAs($this->user('Viewer'))
            ->get(route('cms.enquiries.show', $enquiry))
            ->assertForbidden();

        Log::shouldNotHaveReceived('info', function ($message, $context = []) use ($enquiry) {
            $haystack = json_encode([$message, $context]);

            return str_contains((string) $haystack, $enquiry->email)
                || str_contains((string) $haystack, $enquiry->message);
        });
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function enquiry(): CmsEnquiry
    {
        $enquiry = new CmsEnquiry;
        $enquiry->received_at = now();
        $enquiry->name = 'Jane Citizen';
        $enquiry->email = 'jane.citizen@example.test';
        $enquiry->organisation = 'Community group';
        $enquiry->subject = 'Request for information';
        $enquiry->message = 'Please advise on the clinic timetable.';
        $enquiry->source_hash = hash('sha256', uniqid('', true));
        $enquiry->status = CmsEnquiry::STATUS_NEW;
        $enquiry->save();

        return $enquiry->fresh();
    }
}
