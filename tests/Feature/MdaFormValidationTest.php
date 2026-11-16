<?php

namespace Tests\Feature;

use App\Models\Mda;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MdaFormValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_edit_forms_connect_labels_and_show_focus_styles(): void
    {
        $admin = $this->admin();
        $mda = $this->ministry();

        $create = $this->actingAs($admin)->get(route('mdas.create'));
        $edit = $this->actingAs($admin)->get(route('mdas.edit', $mda));

        $create->assertOk();
        $edit->assertOk();

        foreach ([
            'mda-name',
            'mda-code',
            'mda-email',
            'mda-phone',
            'mda-contact-person',
            'mda-address',
            'mda-focal-name',
            'mda-focal-designation',
            'mda-focal-department',
            'mda-focal-phone',
            'mda-focal-email',
            'mda-focal-supervisor',
            'mda-alternate-name',
            'mda-alternate-phone',
            'mda-alternate-email',
            'mda-status',
        ] as $id) {
            $create->assertSee('for="'.$id.'"', false);
            $create->assertSee('id="'.$id.'"', false);
        }

        foreach ([
            'mda-edit-name',
            'mda-edit-code',
            'mda-edit-email',
            'mda-edit-phone',
            'mda-edit-contact-person',
            'mda-edit-address',
            'mda-edit-focal-name',
            'mda-edit-focal-designation',
            'mda-edit-focal-department',
            'mda-edit-focal-phone',
            'mda-edit-focal-email',
            'mda-edit-focal-supervisor',
            'mda-edit-alternate-name',
            'mda-edit-alternate-phone',
            'mda-edit-alternate-email',
            'mda-edit-status',
        ] as $id) {
            $edit->assertSee('for="'.$id.'"', false);
            $edit->assertSee('id="'.$id.'"', false);
        }

        $create->assertSee('focus:ring-green-700', false);
        $edit->assertSee('focus:ring-green-700', false);
        $create->assertSee('Alternate officer details are the only optional fields.', false);
        $this->assertMatchesRegularExpression('/id="mda-focal-name"[^>]*required/', $create->getContent());
        $this->assertDoesNotMatchRegularExpression('/id="mda-alternate-email"[^>]*required/', $create->getContent());
        $this->assertMatchesRegularExpression('/id="mda-edit-focal-phone"[^>]*required/', $edit->getContent());
        $this->assertDoesNotMatchRegularExpression('/id="mda-edit-alternate-name"[^>]*required/', $edit->getContent());
        $create->assertDontSee('for="mda-email">Email <span class="text-sm font-normal text-gray-500">Optional</span>', false);
        $create->assertSee('for="mda-alternate-name"', false);
        $create->assertSee('Optional', false);
    }

    public function test_submitting_without_compulsory_fields_is_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('mdas.create'))
            ->post(route('mdas.store'), [
                'name' => 'Ministry of Health',
                'code' => 'MOH',
                'status' => 1,
            ])
            ->assertRedirect(route('mdas.create'))
            ->assertSessionHasErrors([
                'email',
                'phone',
                'contact_person',
                'address',
                'focal_name',
                'focal_designation',
                'focal_department',
                'focal_phone',
                'focal_email',
                'focal_supervisor',
            ])
            ->assertSessionDoesntHaveErrors([
                'alternate_name',
                'alternate_phone',
                'alternate_email',
            ]);

        $this->assertDatabaseMissing('mdas', ['code' => 'MOH']);
    }

    public function test_all_compulsory_fields_succeed_without_alternate_officer_details(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('mdas.store'), $this->validMda())
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mdas', [
            'code' => 'MOH',
            'email' => 'health@example.com',
            'focal_name' => 'Ada Okafor',
            'alternate_name' => null,
            'alternate_phone' => null,
            'alternate_email' => null,
        ]);
    }

    public function test_all_compulsory_fields_succeed_with_alternate_officer_details(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('mdas.store'), $this->validMda([
                'code' => 'MOJ',
                'alternate_name' => 'Tunde Ali',
                'alternate_phone' => '08034445555',
                'alternate_email' => 'tunde@example.com',
            ]))
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mdas', [
            'code' => 'MOJ',
            'alternate_name' => 'Tunde Ali',
            'alternate_phone' => '08034445555',
            'alternate_email' => 'tunde@example.com',
        ]);
    }

    public function test_a_ministry_phone_of_30_characters_and_a_valid_email_are_accepted(): void
    {
        $admin = $this->admin();
        $phone = str_repeat('8', 30);

        $this->actingAs($admin)
            ->post(route('mdas.store'), $this->validMda([
                'phone' => $phone,
            ]))
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('success');

        $mda = Mda::where('code', 'MOH')->firstOrFail();
        $updatedPhone = str_repeat('7', 30);

        $this->actingAs($admin)
            ->put(route('mdas.update', $mda), $this->validMda([
                'email' => 'records@example.com',
                'phone' => $updatedPhone,
            ]))
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('success');

        $this->assertSame($updatedPhone, $mda->fresh()->phone);
        $this->assertSame('records@example.com', $mda->fresh()->email);
    }

    public function test_a_phone_longer_than_30_characters_is_rejected(): void
    {
        $admin = $this->admin();
        $phone = str_repeat('8', 31);

        $this->actingAs($admin)
            ->from(route('mdas.create'))
            ->post(route('mdas.store'), $this->validMda(['phone' => $phone]))
            ->assertRedirect(route('mdas.create'))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseMissing('mdas', ['code' => 'MOH']);

        $mda = $this->ministry();

        $this->actingAs($admin)
            ->from(route('mdas.edit', $mda))
            ->put(route('mdas.update', $mda), $this->validMda([
                'name' => 'Ministry of Works',
                'code' => 'MOW',
                'phone' => $phone,
            ]))
            ->assertRedirect(route('mdas.edit', $mda))
            ->assertSessionHasErrors('phone');

        $this->assertNull($mda->fresh()->phone);
    }

    public function test_an_invalid_or_overlong_ministry_email_is_rejected_and_entered_values_remain(): void
    {
        $admin = $this->admin();
        $phone = str_repeat('8', 30);

        $this->actingAs($admin)
            ->from(route('mdas.create'))
            ->post(route('mdas.store'), $this->validMda([
                'email' => 'not-an-email',
                'phone' => $phone,
                'status' => 0,
            ]))
            ->assertRedirect(route('mdas.create'))
            ->assertSessionHasErrors('email')
            ->assertSessionDoesntHaveErrors('phone');

        $this->assertDatabaseMissing('mdas', ['code' => 'MOH']);

        $this->actingAs($admin)
            ->get(route('mdas.create'))
            ->assertSee('value="Ministry of Health"', false)
            ->assertSee('value="'.$phone.'"', false)
            ->assertSee('value="0" selected', false);

        $this->actingAs($admin)
            ->from(route('mdas.create'))
            ->post(route('mdas.store'), $this->validMda([
                'email' => $this->emailOfLength(256),
            ]))
            ->assertRedirect(route('mdas.create'))
            ->assertSessionHasErrors([
                'email' => 'The email field must not be greater than 255 characters.',
            ]);

        $this->assertDatabaseMissing('mdas', ['code' => 'MOH']);
    }

    public function test_the_edit_form_enforces_the_same_compulsory_rules(): void
    {
        $admin = $this->admin();
        $mda = $this->ministry();

        $this->actingAs($admin)
            ->from(route('mdas.edit', $mda))
            ->put(route('mdas.update', $mda), [
                'name' => 'Ministry of Works',
                'code' => 'MOW',
                'status' => 1,
            ])
            ->assertRedirect(route('mdas.edit', $mda))
            ->assertSessionHasErrors(['email', 'focal_name', 'focal_email']);

        $this->assertSame('Ministry of Works', $mda->fresh()->name);
        $this->assertNull($mda->fresh()->email);

        $this->actingAs($admin)
            ->put(route('mdas.update', $mda), $this->validMda([
                'name' => 'Ministry of Works',
                'code' => 'MOW',
                'email' => 'works@example.com',
            ]))
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('success');

        $this->assertSame('works@example.com', $mda->fresh()->email);
        $this->assertSame('Ada Okafor', $mda->fresh()->focal_name);
        $this->assertNull($mda->fresh()->alternate_name);
    }

    public function test_existing_incomplete_mda_records_are_not_altered_until_saved(): void
    {
        $admin = $this->admin();
        $mda = $this->ministry();

        $this->actingAs($admin)
            ->get(route('mdas.edit', $mda))
            ->assertOk();

        $fresh = $mda->fresh();
        $this->assertNull($fresh->email);
        $this->assertNull($fresh->focal_name);
        $this->assertSame('Ministry of Works', $fresh->name);
    }

    public function test_other_roles_cannot_create_or_edit_an_mda(): void
    {
        $officer = User::factory()->create(['role' => 'M&E Officer']);
        $mda = $this->ministry();

        $this->actingAs($officer)
            ->post(route('mdas.store'), $this->validMda([
                'code' => 'NOPE',
            ]))
            ->assertForbidden();

        $this->actingAs($officer)
            ->put(route('mdas.update', $mda), $this->validMda([
                'name' => 'Changed Name',
                'code' => 'MOW',
                'status' => 0,
            ]))
            ->assertForbidden();

        $this->assertDatabaseMissing('mdas', ['code' => 'NOPE']);
        $this->assertSame('Ministry of Works', $mda->fresh()->name);
        $this->assertTrue((bool) $mda->fresh()->status);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'Super Administrator']);
    }

    private function ministry(): Mda
    {
        return Mda::create([
            'name' => 'Ministry of Works',
            'code' => 'MOW',
            'status' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validMda(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ministry of Health',
            'code' => 'MOH',
            'email' => 'health@example.com',
            'phone' => '08031112222',
            'contact_person' => 'Records Officer',
            'address' => 'Alagbaka, Akure',
            'status' => 1,
            'focal_name' => 'Ada Okafor',
            'focal_department' => 'Planning',
            'focal_designation' => 'Director',
            'focal_supervisor' => 'Chief Bola',
            'focal_phone' => '08032223333',
            'focal_email' => 'ada@example.com',
        ], $overrides);
    }

    private function emailOfLength(int $length): string
    {
        $domain = '@example.com';

        return str_repeat('a', $length - strlen($domain)).$domain;
    }
}
