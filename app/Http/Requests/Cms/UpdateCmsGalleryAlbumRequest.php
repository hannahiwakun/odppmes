<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsSlug;
use App\Models\CmsGalleryAlbum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCmsGalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        $album = $this->route('album');

        return $album instanceof CmsGalleryAlbum
            && $this->user()?->can('update', $album) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $album = $this->route('album');
        $id = $album instanceof CmsGalleryAlbum ? $album->id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('cms_gallery_albums', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contentPayload(): array
    {
        $album = $this->route('album');
        $id = $album instanceof CmsGalleryAlbum ? $album->id : null;
        $data = $this->safe()->only(['title', 'description']);
        $data['sort_order'] = (int) $this->input('sort_order', 0);
        $data['slug'] = CmsSlug::unique((string) ($this->input('slug') ?: $this->input('title')), CmsGalleryAlbum::class, $id);

        return $data;
    }
}
