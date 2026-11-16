<?php

namespace App\Http\Requests\Cms;

use App\Models\CmsGalleryItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsGalleryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        return $item instanceof CmsGalleryItem
            && $this->user()?->can('update', $item) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'caption' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contentPayload(): array
    {
        return [
            'caption' => $this->input('caption'),
            'sort_order' => (int) $this->input('sort_order', 0),
        ];
    }
}
