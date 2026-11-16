<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsSlug;
use App\Models\CmsProgrammeHighlight;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCmsProgrammeHighlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        $highlight = $this->route('highlight');

        return $highlight instanceof CmsProgrammeHighlight
            && $this->user()?->can('update', $highlight) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $highlight = $this->route('highlight');
        $id = $highlight instanceof CmsProgrammeHighlight ? $highlight->id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('cms_programme_highlights', 'slug')->ignore($id)],
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
        $highlight = $this->route('highlight');
        $id = $highlight instanceof CmsProgrammeHighlight ? $highlight->id : null;
        $data = $this->safe()->only([
            'title',
            'implementing_mda_name',
            'partner_name',
            'sector',
            'location',
            'public_status',
            'summary',
        ]);
        $data['slug'] = CmsSlug::unique((string) ($this->input('slug') ?: $this->input('title')), CmsProgrammeHighlight::class, $id);

        return $data;
    }
}
