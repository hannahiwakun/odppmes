<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsMediaUploader;
use App\Models\CmsGalleryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreCmsGalleryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CmsGalleryItem::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'caption' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => [
                'required',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(CmsMediaUploader::MAX_BYTES / 1024),
            ],
        ];
    }
}
