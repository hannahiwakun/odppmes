<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsHomepageBannerTypes;
use App\Cms\CmsMediaUploader;
use App\Models\CmsHomepageBanner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreCmsHomepageBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CmsHomepageBanner::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'banner_type' => ['required', 'string', Rule::in(CmsHomepageBannerTypes::keys())],
            'button_text' => ['nullable', 'string', 'max:120'],
            'link_url' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'published_from' => ['nullable', 'date'],
            'published_until' => ['nullable', 'date', 'after_or_equal:published_from'],
            'image_alt' => ['nullable', 'string', 'max:255'],
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
        $data = $this->safe()->only([
            'title',
            'body',
            'banner_type',
            'button_text',
            'link_url',
            'image_alt',
            'published_from',
            'published_until',
        ]);
        $data['sort_order'] = (int) $this->input('sort_order', 0);

        return $data;
    }
}
