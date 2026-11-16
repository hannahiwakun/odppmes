<?php

namespace App\Providers;

use App\Cms\CmsContentTypes;
use App\Cms\CmsPermissions;
use App\Models\CmsEnquiry;
use App\Models\CmsMediaAsset;
use App\Models\CmsWorkflowEvent;
use App\Models\Mda;
use App\Models\Project;
use App\Models\User;
use App\Policies\CmsEnquiryPolicy;
use App\Policies\CmsMediaAssetPolicy;
use App\Policies\CmsWorkflowContentPolicy;
use App\Policies\CmsWorkflowEventPolicy;
use App\Policies\MdaPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Mda::class, MdaPolicy::class);
        Gate::policy(CmsMediaAsset::class, CmsMediaAssetPolicy::class);
        Gate::policy(CmsEnquiry::class, CmsEnquiryPolicy::class);
        Gate::policy(CmsWorkflowEvent::class, CmsWorkflowEventPolicy::class);

        foreach (CmsContentTypes::MAP as $class) {
            Gate::policy($class, CmsWorkflowContentPolicy::class);
        }

        Relation::morphMap(CmsContentTypes::MAP);

        Gate::define('access-cms', fn (User $user) => CmsPermissions::canAccessCms($user));
        Gate::define('manage-cms-users', fn (User $user) => CmsPermissions::canManageUsers($user));
        Gate::define('review-cms', fn (User $user) => CmsPermissions::canReview($user));
        Gate::define('publish-cms', fn (User $user) => CmsPermissions::canPublish($user));
        Gate::define('archive-cms', fn (User $user) => CmsPermissions::canArchive($user));
    }
}
