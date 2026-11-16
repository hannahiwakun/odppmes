<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsMediaUploader;
use App\Cms\CmsSlug;
use App\Models\CmsManagementProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreCmsManagementProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CmsManagementProfile::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('cms_management_profiles', 'slug')],
            'designation' => ['required', 'string', 'max:255'],
            'biography' => ['required', 'string', 'min:20'],
            'responsibilities' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
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
        $data = $this->safe()->only(['full_name', 'designation', 'biography', 'responsibilities']);
        $data['sort_order'] = (int) $this->input('sort_order', 0);
        $data['slug'] = CmsSlug::unique((string) ($this->input('slug') ?: $this->input('full_name')), CmsManagementProfile::class);

        return $data;
    }
}
