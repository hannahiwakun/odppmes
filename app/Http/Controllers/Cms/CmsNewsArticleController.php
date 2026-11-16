<?php

namespace App\Http\Controllers\Cms;

use App\Cms\CmsMediaUploader;
use App\Cms\CmsNewsCategories;
use App\Http\Controllers\Cms\Concerns\AppliesCmsListingFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreCmsNewsArticleRequest;
use App\Http\Requests\Cms\UpdateCmsNewsArticleRequest;
use App\Models\CmsNewsArticle;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class CmsNewsArticleController extends Controller
{
    use AppliesCmsListingFilters;
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CmsNewsArticle::class);

        $query = CmsNewsArticle::query()->with(['createdBy', 'featuredImage']);
        $articles = $this->applyListingFilters($query, $request, ['title', 'slug', 'excerpt', 'category']);

        $category = $request->string('category')->toString();
        if ($category !== '' && CmsNewsCategories::isValid($category)) {
            $articles->where('category', $category);
        }

        $articles = $articles->paginate(15)->withQueryString();

        return view('cms.news.index', compact('articles'));
    }

    public function create(): View
    {
        $this->authorize('create', CmsNewsArticle::class);

        return view('cms.news.create');
    }

    public function store(StoreCmsNewsArticleRequest $request, CmsMediaUploader $uploader): RedirectResponse
    {
        $article = new CmsNewsArticle;
        $article->fill($request->contentPayload());
        $article->created_by = $request->user()->id;
        $article->updated_by = $request->user()->id;
        $this->attachFeaturedImage($request->file('image'), $request->user(), $uploader, $article);
        $article->save();

        return redirect()
            ->route('cms.news.show', $article)
            ->with('success', 'News article saved as a draft.');
    }

    public function show(CmsNewsArticle $article): View
    {
        $this->authorize('view', $article);

        $article->load(['createdBy', 'updatedBy', 'reviewedBy', 'publishedBy', 'featuredImage', 'workflowEvents.actor']);

        return view('cms.news.show', compact('article'));
    }

    public function preview(CmsNewsArticle $article): View
    {
        $this->authorize('view', $article);

        $article->load(['createdBy', 'featuredImage']);

        return view('cms.news.preview', compact('article'));
    }

    public function edit(CmsNewsArticle $article): View
    {
        $this->authorize('update', $article);
        $article->load('featuredImage');

        return view('cms.news.edit', compact('article'));
    }

    public function update(UpdateCmsNewsArticleRequest $request, CmsNewsArticle $article, CmsMediaUploader $uploader): RedirectResponse
    {
        $article->fill($request->contentPayload());
        $article->updated_by = $request->user()->id;
        $this->attachFeaturedImage($request->file('image'), $request->user(), $uploader, $article);
        $article->save();

        return redirect()
            ->route('cms.news.show', $article)
            ->with('success', 'News article updated.');
    }

    public function destroy(CmsNewsArticle $article): RedirectResponse
    {
        $this->authorize('delete', $article);
        $article->delete();

        return redirect()
            ->route('cms.news.index')
            ->with('success', 'News article removed.');
    }

    private function attachFeaturedImage(?UploadedFile $file, User $user, CmsMediaUploader $uploader, CmsNewsArticle $article): void
    {
        if (! $file instanceof UploadedFile) {
            return;
        }

        $article->featured_image_media_id = $uploader->store($file, $user)->id;
    }
}
