<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MdaController;
use App\Http\Controllers\DevelopmentPartnerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\MonitoringEvidenceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\ProjectPhotoController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\PublicSiteController;
use App\Cms\CmsContentTypes;
use App\Http\Controllers\Cms\CmsApprovalCentreController;
use App\Http\Controllers\Cms\CmsEnquiryController;
use App\Http\Controllers\Cms\CmsGalleryAlbumController;
use App\Http\Controllers\Cms\CmsGalleryItemController;
use App\Http\Controllers\Cms\CmsHomepageBannerController;
use App\Http\Controllers\Cms\CmsManagementProfileController;
use App\Http\Controllers\Cms\CmsMediaClearController;
use App\Http\Controllers\Cms\CmsMediaPreviewController;
use App\Http\Controllers\Cms\CmsNewsArticleController;
use App\Http\Controllers\Cms\CmsOfficeContactController;
use App\Http\Controllers\Cms\CmsProgrammeHighlightController;
use App\Http\Controllers\Cms\CmsPublicPartnerController;
use App\Http\Controllers\Cms\CmsUserDirectoryController;
use App\Http\Controllers\CmsHomeController;
use App\Http\Controllers\CmsWorkflowController;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/about', [PublicSiteController::class, 'about'])->name('public.about');
Route::get('/services', [PublicSiteController::class, 'services'])->name('public.services');
Route::get('/partners', [PublicSiteController::class, 'partners'])->name('public.partners');
Route::get('/projects-programmes', [PublicSiteController::class, 'projects'])->name('public.projects');
Route::get('/gallery', [PublicSiteController::class, 'gallery'])->name('public.gallery');
Route::get('/management-team', [PublicSiteController::class, 'management'])->name('public.management');
Route::get('/news', [PublicSiteController::class, 'news'])->name('public.news');
Route::get('/news/{slug}', [PublicSiteController::class, 'newsShow'])->name('public.news.show');
Route::get('/media/{uuid}', [PublicMediaController::class, 'show'])
    ->where('uuid', '[0-9a-fA-F-]{36}')
    ->name('public.media');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('public.contact');
Route::post('/contact', [PublicSiteController::class, 'submitContact'])
    ->middleware('throttle:5,1')
    ->name('public.contact.submit');

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('cms.dashboard')
        ->name('dashboard');

    Route::middleware([
        'role:Super Administrator,DPMO Website Administrator',
        'cms.access',
    ])->prefix('cms')->name('cms.')->group(function () {
        Route::get('/', [CmsHomeController::class, 'index'])->name('home');
        Route::get('approvals', [CmsApprovalCentreController::class, 'index'])->name('approvals.index');
        Route::get('users', [CmsUserDirectoryController::class, 'index'])->name('users.index');
        Route::get('media/{asset}/preview', [CmsMediaPreviewController::class, 'show'])
            ->name('media.preview');
        Route::post('media/{asset}/clear-for-public', [CmsMediaClearController::class, 'store'])
            ->name('media.clear-for-public');

        Route::get('news/{article}/preview', [CmsNewsArticleController::class, 'preview'])
            ->name('news.preview');
        Route::resource('news', CmsNewsArticleController::class)
            ->parameters(['news' => 'article']);

        Route::resource('gallery-albums', CmsGalleryAlbumController::class)
            ->parameters(['gallery-albums' => 'album']);
        Route::post('gallery-albums/{album}/items', [CmsGalleryItemController::class, 'store'])
            ->scopeBindings()
            ->name('gallery-albums.items.store');
        Route::put('gallery-albums/{album}/items/{item}', [CmsGalleryItemController::class, 'update'])
            ->scopeBindings()
            ->name('gallery-albums.items.update');
        Route::delete('gallery-albums/{album}/items/{item}', [CmsGalleryItemController::class, 'destroy'])
            ->scopeBindings()
            ->name('gallery-albums.items.destroy');

        Route::resource('management-profiles', CmsManagementProfileController::class)
            ->parameters(['management-profiles' => 'profile']);
        Route::resource('public-partners', CmsPublicPartnerController::class)
            ->parameters(['public-partners' => 'partner']);
        Route::resource('programme-highlights', CmsProgrammeHighlightController::class)
            ->parameters(['programme-highlights' => 'highlight']);
        Route::resource('homepage-banners', CmsHomepageBannerController::class)
            ->parameters(['homepage-banners' => 'banner']);

        Route::get('office-contact', [CmsOfficeContactController::class, 'show'])->name('office-contact.show');
        Route::get('office-contact/edit', [CmsOfficeContactController::class, 'edit'])->name('office-contact.edit');
        Route::post('office-contact', [CmsOfficeContactController::class, 'store'])->name('office-contact.store');
        Route::put('office-contact/{office_contact}', [CmsOfficeContactController::class, 'update'])->name('office-contact.update');

        Route::get('enquiries', [CmsEnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('enquiries/{enquiry}', [CmsEnquiryController::class, 'show'])->name('enquiries.show');
        Route::patch('enquiries/{enquiry}', [CmsEnquiryController::class, 'update'])->name('enquiries.update');

        $workflow = function (string $uri, string $action, string $name) {
            Route::post($uri, [CmsWorkflowController::class, $action])
                ->whereIn('type', CmsContentTypes::keys())
                ->whereNumber('id')
                ->name($name);
        };

        $workflow('{type}/{id}/submit', 'submit', 'workflow.submit');
        $workflow('{type}/{id}/approve', 'approve', 'workflow.approve');
        $workflow('{type}/{id}/reject', 'reject', 'workflow.reject');
        $workflow('{type}/{id}/publish', 'publish', 'workflow.publish');
        $workflow('{type}/{id}/unpublish', 'unpublish', 'workflow.unpublish');
        $workflow('{type}/{id}/archive', 'archive', 'workflow.archive');
        $workflow('{type}/{id}/restore', 'restore', 'workflow.restore');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | MDAs
    | Super Administrator: full management.
    | M&E Officer: directory and profiles, read-only.
    | MDA Officer: own profile only (no directory).
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Super Administrator')->group(function () {
        Route::patch(
            'mdas/{mda}/deactivate',
            [MdaController::class, 'deactivate']
        )->name('mdas.deactivate');

        Route::resource('mdas', MdaController::class)->except(['index', 'show']);

        Route::resource(
            'development-partners',
            DevelopmentPartnerController::class
        );
    });

    Route::middleware('role:Super Administrator,M&E Officer')->group(function () {
        Route::get('mdas', [MdaController::class, 'index'])->name('mdas.index');
        Route::get('mdas/{mda}', [MdaController::class, 'show'])->name('mdas.show');
    });

    Route::middleware('role:MDA Officer')->group(function () {
        Route::get('my-mda', [MdaController::class, 'mine'])->name('mdas.mine');
    });

    /*
    |--------------------------------------------------------------------------
    | Projects and photos
    | Same roles that can see Projects in the menu.
    | Deletion is limited further in the controller.
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role:Super Administrator,M&E Officer,MDA Officer',
        'mda.project',
    ])->group(function () {
        Route::resource('projects', ProjectController::class)
            ->except(['destroy']);

        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])
            ->middleware('role:Super Administrator,M&E Officer')
            ->name('projects.destroy');

        Route::resource(
            'projects.photos',
            ProjectPhotoController::class
        )->only([
            'index',
            'create',
            'store',
        ])->scoped();

        Route::delete(
            'projects/{project}/photos/{photo}',
            [ProjectPhotoController::class, 'destroy']
        )->middleware('role:Super Administrator,M&E Officer')
            ->scopeBindings()
            ->name('projects.photos.destroy');

        Route::get(
            'projects/{project}/photos/{photo}/file',
            [ProjectPhotoController::class, 'file']
        )->scopeBindings()->name('projects.photos.file');
    });

    /*
    |--------------------------------------------------------------------------
    | Monitoring
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role:Super Administrator,M&E Officer,MDA Officer',
        'mda.project',
    ])->group(function () {
        Route::get(
            'monitorings/{monitoring}/photo',
            [MonitoringController::class, 'photo']
        )->name('monitorings.photo');

        Route::post(
            'monitorings/{monitoring}/evidence',
            [MonitoringEvidenceController::class, 'store']
        )->name('monitorings.evidence.store');

        Route::get(
            'monitorings/{monitoring}/evidence/{monitoringEvidence}/file',
            [MonitoringEvidenceController::class, 'file']
        )->name('monitorings.evidence.file');

        Route::get(
            'monitorings/{monitoring}/evidence/{monitoringEvidence}/download',
            [MonitoringEvidenceController::class, 'download']
        )->name('monitorings.evidence.download');

        Route::delete(
            'monitorings/{monitoring}/evidence/{monitoringEvidence}',
            [MonitoringEvidenceController::class, 'destroy']
        )->middleware('role:Super Administrator,M&E Officer')
            ->name('monitorings.evidence.destroy');

        Route::resource('monitorings', MonitoringController::class)
            ->except(['destroy']);

        Route::delete('monitorings/{monitoring}', [MonitoringController::class, 'destroy'])
            ->middleware('role:Super Administrator,M&E Officer')
            ->name('monitorings.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Project Documents
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role:Super Administrator,M&E Officer,MDA Officer',
        'mda.project',
    ])->group(function () {

        Route::get(
            '/projects/{project}/documents',
            [ProjectDocumentController::class, 'index']
        )->name('project-documents.index');

        Route::get(
            '/projects/{project}/documents/create',
            [ProjectDocumentController::class, 'create']
        )->name('project-documents.create');

        Route::post(
            '/projects/{project}/documents',
            [ProjectDocumentController::class, 'store']
        )->name('project-documents.store');

        Route::get(
            '/projects/{project}/documents/{projectDocument}/download',
            [ProjectDocumentController::class, 'download']
        )->name('project-documents.download');

        Route::delete(
            '/projects/{project}/documents/{projectDocument}',
            [ProjectDocumentController::class, 'destroy']
        )->middleware('role:Super Administrator,M&E Officer')
            ->name('project-documents.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role:Super Administrator,M&E Officer,Viewer,MDA Officer'
    ])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/reports/pdf', [ReportController::class, 'pdf'])
            ->name('reports.pdf');
    });

    Route::middleware([
        'role:Super Administrator,M&E Officer,Viewer'
    ])->group(function () {
        Route::get('/project-map', [MapController::class, 'index'])
            ->name('project.map');
    });

    /*
    |--------------------------------------------------------------------------
    | Super Administrator Routes
    |--------------------------------------------------------------------------
    */

        Route::middleware([
        'role:Super Administrator'
    ])->group(function () {

        Route::resource('users', UserController::class);

        Route::get(
            '/audit-logs',
            [AuditLogController::class, 'index']
        )->name('audit.logs');

        Route::view('/office/circular', 'office.circular')->name('office.circular');
        Route::view('/office/mda-return', 'office.mda-return')->name('office.mda-return');

    });


}); // closes main auth middleware group


require __DIR__.'/auth.php';