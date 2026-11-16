<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsMediaUploader;
use App\Cms\CmsPublicPartnerCategories;
use App\Cms\CmsSlug;
use App\Models\CmsPublicPartner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateCmsPublicPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $partner = $this->route('partner');

        return $partner instanceof CmsPublicPartner
            && $this->user()?->can('update', $partner) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $partner = $this->route('partner');
        $id = $partner instanceof CmsPublicPartner ? $partner->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('cms_public_partners', 'slug')->ignore($id)],
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
        $partner = $this->route('partner');
        $id = $partner instanceof CmsPublicPartner ? $partner->id : null;
        $data = $this->safe()->only(['name', 'category', 'summary', 'website_url']);
        $data['slug'] = CmsSlug::unique((string) ($this->input('slug') ?: $this->input('name')), CmsPublicPartner::class, $id);

        return $data;
    }
}
