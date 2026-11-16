<?php

namespace App\Http\Requests\Cms;

use App\Models\CmsEnquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCmsEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enquiry = $this->route('enquiry');

        return $enquiry instanceof CmsEnquiry
            && $this->user()?->can('update', $enquiry) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                CmsEnquiry::STATUS_NEW,
                CmsEnquiry::STATUS_IN_REVIEW,
                CmsEnquiry::STATUS_CLOSED,
                CmsEnquiry::STATUS_SPAM,
            ])],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
