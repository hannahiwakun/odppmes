<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsSlug;
use App\Models\CmsProgrammeHighlight;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCmsProgrammeHighlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CmsProgrammeHighlight::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('cms_programme_highlights', 'slug')],
            'implementing_mda_name' => ['required', 'string', 'max:255'],
            'partner_name' => ['required', 'string', 'max:255'],
            'sector' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'public_status' => ['required', 'string', 'max:100'],
            'summary' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contentPayload(): array
    {
        $data = $this->safe()->only([
            'title',
            'implementing_mda_name',
            'partner_name',
            'sector',
            'location',
            'public_status',
            'summary',
        ]);
        $data['slug'] = CmsSlug::unique((string) ($this->input('slug') ?: $this->input('title')), CmsProgrammeHighlight::class);

        return $data;
    }
}
