<?php

namespace App\Http\Requests\Cms;

use App\Models\CmsOfficeContact;
use Illuminate\Foundation\Http\FormRequest;

class UpsertCmsOfficeContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $contact = $this->route('office_contact');

        if ($contact instanceof CmsOfficeContact) {
            return $this->user()?->can('update', $contact) === true;
        }

        return $this->user()?->can('create', CmsOfficeContact::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'hours' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contentPayload(): array
    {
        return $this->safe()->only(['address', 'phone', 'email', 'hours']);
    }
}
