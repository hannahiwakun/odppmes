<?php

namespace App\Http\Requests\Cms;

use App\Cms\CmsMediaUploader;
use App\Cms\CmsNewsCategories;
use App\Cms\CmsNewsHtml;
use App\Cms\CmsSlug;
use App\Models\CmsNewsArticle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateCmsNewsArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $article = $this->route('article');

        return $article instanceof CmsNewsArticle
            && $this->user()?->can('update', $article) === true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('body')) {
            $merge['body'] = CmsNewsHtml::normalizeIncoming((string) $this->input('body'));
        }

        if (! $this->filled('category')) {
            $merge['category'] = CmsNewsCategories::DEFAULT;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $article = $this->route('article');
        $id = $article instanceof CmsNewsArticle ? $article->id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('cms_news_articles', 'slug')->ignore($id)],
            'category' => ['required', 'string', 'max:100', Rule::in(CmsNewsCategories::all())],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string', 'min:20'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'image' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(CmsMediaUploader::MAX_BYTES / 1024),
            ],
            'published_from' => ['nullable', 'date'],
            'published_until' => ['nullable', 'date', 'after_or_equal:published_from'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contentPayload(): array
    {
        $article = $this->route('article');
        $id = $article instanceof CmsNewsArticle ? $article->id : null;
        $data = $this->safe()->only(['title', 'excerpt', 'body', 'category', 'featured_image_alt', 'published_from', 'published_until']);
        $source = $this->input('slug') ?: $this->input('title');
        $data['slug'] = CmsSlug::unique((string) $source, CmsNewsArticle::class, $id);

        return $data;
    }
}
