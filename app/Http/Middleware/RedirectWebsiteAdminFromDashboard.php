<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectWebsiteAdminFromDashboard
{
    /**
     * Website Administrators must not run the operational dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isWebsiteAdmin()) {
            return redirect()->route('cms.home');
        }

        return $next($request);
    }
}
