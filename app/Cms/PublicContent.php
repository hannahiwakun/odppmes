<?php

namespace App\Cms;

use App\Cms\CmsNewsCategories;
use App\Models\CmsGalleryAlbum;
use App\Models\CmsHomepageBanner;
use App\Models\CmsManagementProfile;
use App\Models\CmsMediaAsset;
use App\Models\CmsNewsArticle;
use App\Models\CmsOfficeContact;
use App\Models\CmsProgrammeHighlight;
use App\Models\CmsPublicPartner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class PublicContent
{
    public function banners(int $limit = 12): Collection
    {
        return CmsHomepageBanner::query()
            ->published()
            ->with(['image' => fn ($query) => $this->clearedMedia($query)])
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function newsList(Request $request, int $perPage = 9): LengthAwarePaginator
    {
        $query = CmsNewsArticle::query()
            ->published()
            ->with(['featuredImage' => fn ($relation) => $this->clearedMedia($relation)])
            ->latest('published_at');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('title', 'like', $term)
                    ->orWhere('excerpt', 'like', $term);
            });
        }

        $category = $request->string('category')->toString();
        if ($category !== '' && CmsNewsCategories::isValid($category)) {
            $query->where('category', $category);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function latestNews(int $limit = 3): Collection
    {
        return CmsNewsArticle::query()
            ->published()
            ->with(['featuredImage' => fn ($relation) => $this->clearedMedia($relation)])
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function newsBySlug(string $slug): ?CmsNewsArticle
    {
        return CmsNewsArticle::query()
            ->published()
            ->with([
                'createdBy',
                'featuredImage' => fn ($relation) => $this->clearedMedia($relation),
            ])
            ->where('slug', $slug)
            ->first();
    }

    public function newsSlugExists(string $slug): bool
    {
        return CmsNewsArticle::withTrashed()->where('slug', $slug)->exists();
    }

    public function relatedNews(CmsNewsArticle $article, int $limit = 4): Collection
    {
        return CmsNewsArticle::query()
            ->published()
            ->with(['featuredImage' => fn ($relation) => $this->clearedMedia($relation)])
            ->whereKeyNot($article->id)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function albums(int $perPage = 9): LengthAwarePaginator
    {
        return CmsGalleryAlbum::query()
            ->published()
            ->with(['publicItems.media'])
            ->orderBy('sort_order')
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function latestAlbums(int $limit = 3): Collection
    {
        return CmsGalleryAlbum::query()
            ->published()
            ->with(['publicItems.media'])
            ->orderBy('sort_order')
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function profiles(): Collection
    {
        return CmsManagementProfile::query()
            ->published()
            ->with(['photo' => fn ($query) => $this->clearedMedia($query)])
            ->orderBy('sort_order')
            ->orderBy('full_name')
            ->get();
    }

    public function partners(int $limit = 12): Collection
    {
        return CmsPublicPartner::query()
            ->published()
            ->with(['logo' => fn ($query) => $this->clearedMedia($query)])
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function highlights(int $limit = 12): Collection
    {
        return CmsProgrammeHighlight::query()
            ->published()
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function officeContact(): ?CmsOfficeContact
    {
        return CmsOfficeContact::query()->published()->first();
    }

    private function clearedMedia(Relation $query): Relation
    {
        return $query->where('is_cleared_for_public', true)
            ->where('disk', CmsMediaAsset::DISK);
    }
}
