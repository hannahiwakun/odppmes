<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsSlug;
use App\Models\CmsGalleryAlbum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCmsGalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CmsGalleryAlbum::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('cms_gallery_albums', 'slug')],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contentPayload(): array
    {
        $data = $this->safe()->only(['title', 'description']);
        $data['sort_order'] = (int) $this->input('sort_order', 0);
        $data['slug'] = CmsSlug::unique((string) ($this->input('slug') ?: $this->input('title')), CmsGalleryAlbum::class);

        return $data;
    }
}
