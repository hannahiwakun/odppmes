<?php

namespace App\Http\Middleware;

use App\Models\Monitoring;
use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMdaProjectAccess
{
    /**
     * MDA Officers may open only projects led by their assigned MDA.
     * A missing project returns 404 so a guessed id is not confirmed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isMdaOfficer()) {
            return $next($request);
        }

        $project = $request->route('project');

        if ($project instanceof Project && ! $user->canAccessProject($project)) {
            abort(404);
        }

        $monitoring = $request->route('monitoring');

        if ($monitoring instanceof Monitoring) {
            $monitoring->loadMissing('project');

            if (! $user->canAccessProject($monitoring->project)) {
                abort(404);
            }
        }

        return $next($request);
    }
}
