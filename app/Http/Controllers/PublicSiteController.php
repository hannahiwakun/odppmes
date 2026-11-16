<?php

namespace App\Http\Controllers;

use App\Cms\PublicContent;
use App\Cms\PublicEnquiryRecorder;
use App\Models\CmsHomepageBanner;
use App\Models\CmsNewsArticle;
use Illuminate\Http\Request;
use RuntimeException;

class PublicSiteController extends Controller
{
    public function __construct(
        private readonly PublicContent $cms,
        private readonly PublicEnquiryRecorder $enquiries,
    ) {
    }

    public function home()
    {
        $banners = $this->cms->banners();

        return view('public.home', $this->site() + [
            'banners' => $banners,
            'heroBanners' => $banners->where('banner_type', CmsHomepageBanner::TYPE_HERO_NOTICE)->values(),
            'alertBanners' => $banners->where('banner_type', CmsHomepageBanner::TYPE_ALERT)->values(),
            'featureBanners' => $banners->where('banner_type', CmsHomepageBanner::TYPE_FEATURE)->values(),
            'homeNews' => $this->cms->latestNews(3),
            'homeHighlights' => $this->cms->highlights(3),
            'homeAlbums' => $this->cms->latestAlbums(3),
            'homePartners' => $this->cms->partners(3),
            'homeProfiles' => $this->cms->profiles()->take(1),
        ]);
    }

    public function about()
    {
        return view('public.about', $this->site());
    }

    public function services()
    {
        return view('public.services', $this->site());
    }

    public function partners()
    {
        return view('public.partners', $this->site() + [
            'partners' => $this->cms->partners(),
        ]);
    }

    public function projects()
    {
        return view('public.projects', $this->site() + [
            'highlights' => $this->cms->highlights(),
        ]);
    }

    public function gallery()
    {
        return view('public.gallery', $this->site() + [
            'albums' => $this->cms->albums(),
        ]);
    }

    public function management()
    {
        return view('public.management', $this->site() + [
            'profiles' => $this->cms->profiles(),
        ]);
    }

    public function news(Request $request)
    {
        $articles = $this->cms->newsList($request);

        return view('public.news-index', [
            'articles' => $articles,
            'usingCms' => $articles->total() > 0 || $request->filled('q') || $request->filled('category'),
            'fallbackArticles' => $this->fallbackArticles(),
        ] + $this->site());
    }

    public function newsShow(string $slug)
    {
        $article = $this->cms->newsBySlug($slug);

        if ($article instanceof CmsNewsArticle) {
            return view('public.news-show', [
                'article' => $article,
                'related' => $this->cms->relatedNews($article),
                'fromCms' => true,
            ] + $this->site());
        }

        if ($this->cms->newsSlugExists($slug)) {
            abort(404);
        }

        $fallback = collect($this->fallbackArticles())->firstWhere('slug', $slug);
        abort_if($fallback === null, 404);

        $related = collect($this->fallbackArticles())
            ->reject(fn (array $item) => $item['slug'] === $slug)
            ->values()
            ->all();

        return view('public.news-show', [
            'article' => $fallback,
            'related' => $related,
            'fromCms' => false,
        ] + $this->site());
    }

    public function contact()
    {
        return view('public.contact', $this->site() + [
            'officeContact' => $this->cms->officeContact(),
        ]);
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'organisation' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:20|max:2000',
            'website' => 'nullable|size:0',
        ]);

        try {
            $this->enquiries->record($validated, $request->ip());
        } catch (RuntimeException) {
            return back()
                ->withInput()
                ->withErrors([
                    'message' => 'Your enquiry could not be fully recorded. Please try again shortly.',
                ]);
        }

        return redirect()
            ->route('public.contact')
            ->with('success', 'Your enquiry has been received. The Office will review it. This form is not a confirmed official mailbox until DPMO publishes contact details.');
    }

    /**
     * @return array<string, mixed>
     */
    private function site(): array
    {
        return ['site' => config('public_site')];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fallbackArticles(): array
    {
        return config('public_site.news', []);
    }
}
