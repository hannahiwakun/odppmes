<?php

namespace App\Http\Controllers;

use App\Cms\CmsContentTypes;
use App\Cms\CmsWorkflowStatus;
use App\Models\CmsEnquiry;
use App\Models\CmsGalleryAlbum;
use App\Models\CmsHomepageBanner;
use App\Models\CmsManagementProfile;
use App\Models\CmsMediaAsset;
use App\Models\CmsNewsArticle;
use App\Models\CmsProgrammeHighlight;
use App\Models\CmsPublicPartner;
use App\Models\CmsWorkflowEvent;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CmsHomeController extends Controller
{
    public function index(): View
    {
        Gate::authorize('access-cms');

        $news = CmsNewsArticle::query();

        $stats = [
            'news_total' => (clone $news)->count(),
            'news_published' => (clone $news)->where('status', CmsWorkflowStatus::PUBLISHED)->count(),
            'news_draft' => (clone $news)->where('status', CmsWorkflowStatus::DRAFT)->count(),
            'news_submitted' => (clone $news)->where('status', CmsWorkflowStatus::SUBMITTED)->count(),
            'news_rejected' => (clone $news)->where('status', CmsWorkflowStatus::REJECTED)->count(),
            'albums' => CmsGalleryAlbum::query()->count(),
            'albums_published' => CmsGalleryAlbum::query()->where('status', CmsWorkflowStatus::PUBLISHED)->count(),
            'albums_draft' => CmsGalleryAlbum::query()->where('status', CmsWorkflowStatus::DRAFT)->count(),
            'media' => CmsMediaAsset::query()->count(),
            'profiles' => CmsManagementProfile::query()->count(),
            'profiles_published' => CmsManagementProfile::query()->where('status', CmsWorkflowStatus::PUBLISHED)->count(),
            'profiles_draft' => CmsManagementProfile::query()->where('status', CmsWorkflowStatus::DRAFT)->count(),
            'partners' => CmsPublicPartner::query()->count(),
            'partners_published' => CmsPublicPartner::query()->where('status', CmsWorkflowStatus::PUBLISHED)->count(),
            'partners_draft' => CmsPublicPartner::query()->where('status', CmsWorkflowStatus::DRAFT)->count(),
            'highlights' => CmsProgrammeHighlight::query()->count(),
            'highlights_published' => CmsProgrammeHighlight::query()->where('status', CmsWorkflowStatus::PUBLISHED)->count(),
            'highlights_draft' => CmsProgrammeHighlight::query()->where('status', CmsWorkflowStatus::DRAFT)->count(),
            'banners' => CmsHomepageBanner::query()->count(),
            'banners_published' => CmsHomepageBanner::query()->where('status', CmsWorkflowStatus::PUBLISHED)->count(),
            'banners_draft' => CmsHomepageBanner::query()->where('status', CmsWorkflowStatus::DRAFT)->count(),
            'enquiries_new' => CmsEnquiry::query()->where('status', CmsEnquiry::STATUS_NEW)->count(),
            'pending_review' => $this->pendingReviewCount(),
        ];

        $recentEvents = CmsWorkflowEvent::query()
            ->with('actor')
            ->latest('id')
            ->limit(8)
            ->get();

        $labels = CmsContentTypes::labels();

        return view('cms.home', compact('stats', 'recentEvents', 'labels'));
    }

    private function pendingReviewCount(): int
    {
        $total = 0;

        foreach (CmsContentTypes::MAP as $class) {
            $total += $class::query()->where('status', CmsWorkflowStatus::SUBMITTED)->count();
        }

        return $total;
    }
}
