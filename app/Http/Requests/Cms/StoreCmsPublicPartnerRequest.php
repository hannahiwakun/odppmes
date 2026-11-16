<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsMediaUploader;
use App\Cms\CmsPublicPartnerCategories;
use App\Cms\CmsSlug;
use App\Models\CmsPublicPartner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreCmsPublicPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CmsPublicPartner::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('cms_public_partners', 'slug')],
            'category' => ['required', 'string', Rule::in(CmsPublicPartnerCategories::allowed())],
            'summary' => ['required', 'string', 'min:10', 'max:5000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'image' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(CmsMediaUploader::MAX_BYTES / 1024),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contentPayload(): array
    {
        $data = $this->safe()->only(['name', 'category', 'summary', 'website_url']);
        $data['slug'] = CmsSlug::unique((string) ($this->input('slug') ?: $this->input('name')), CmsPublicPartner::class);

        return $data;
    }
}
