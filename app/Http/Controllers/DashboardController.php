<?php

namespace App\Http\Controllers;

use App\Models\Mda;
use App\Models\Project;
use App\Models\Monitoring;
use App\Models\DevelopmentPartner;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $viewer = auth()->user();
        $scopedProjects = Project::query()->visibleTo($viewer);
        $scopedMonitorings = Monitoring::query()->whereHas(
            'project',
            fn ($query) => $query->visibleTo($viewer)
        );

        /*
        |--------------------------------------------------------------------------
        | Executive Summary Statistics
        |--------------------------------------------------------------------------
        */

        $totalProjects = (clone $scopedProjects)->count();

        $totalMDAs = 0;
        $totalPartners = 0;

        if (! $viewer->isMdaOfficer() && ! $viewer->isDevelopmentPartner()) {
            $totalMDAs = Mda::count();
            $totalPartners = DevelopmentPartner::count();
        }

        $totalMonitoring = (clone $scopedMonitorings)->count();


        /*
        |--------------------------------------------------------------------------
        | Financial Summary
        |--------------------------------------------------------------------------
        */

        $totalBudget = (clone $scopedProjects)->sum('budget');


        /*
        |--------------------------------------------------------------------------
        | Project Status Summary
        |--------------------------------------------------------------------------
        */

        $planningProjects = (clone $scopedProjects)->where('status','Planning')->count();

        $ongoingProjects = (clone $scopedProjects)->where('status','Ongoing')->count();

        $completedProjects = (clone $scopedProjects)->where('status','Completed')->count();

        $suspendedProjects = (clone $scopedProjects)->where('status','Suspended')->count();

        $cancelledProjects = (clone $scopedProjects)->where('status','Cancelled')->count();



        /*
        |--------------------------------------------------------------------------
        | Performance Indicators
        |--------------------------------------------------------------------------
        */

        $activeProjects = (clone $scopedProjects)->whereIn('status', [
            'Planning',
            'Ongoing',
            'Suspended',
        ]);

        $onTrackProjects = (clone $activeProjects)->where('progress', '>=', 70)->count();

        $attentionProjects = (clone $activeProjects)->whereBetween('progress', [30, 69])->count();

        $lowProgressProjects = (clone $activeProjects)->where('progress', '<', 30)->count();

        $registerDelayedProjects = (clone $scopedProjects)
            ->where('initiative_status', 'Delayed')
            ->count();



        /*
        |--------------------------------------------------------------------------
        | Average Project Progress
        |--------------------------------------------------------------------------
        */

        $averageProgress = round((clone $scopedProjects)->avg('progress') ?? 0,1);



        /*
        |--------------------------------------------------------------------------
        | Recent Monitoring Activities
        |--------------------------------------------------------------------------
        */

        $recentMonitorings = (clone $scopedMonitorings)->with([
                'project',
                'user'
            ])
            ->latest()
            ->take(5)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Projects By Sector
        |--------------------------------------------------------------------------
        */

        $sectorData = (clone $scopedProjects)->select(
                'sector',
                DB::raw('count(*) as total')
            )
            ->groupBy('sector')
            ->orderByDesc('total')
            ->get();


        $sectorLabels = $sectorData->pluck('sector');

        $sectorTotals = $sectorData->pluck('total');



        /*
        |--------------------------------------------------------------------------
        | Projects By MDA
        |--------------------------------------------------------------------------
        */

        $mdaProjects = collect();
        $partnerProjects = collect();

        if (! $viewer->isMdaOfficer()) {
            $mdaProjects = (clone $scopedProjects)->with('mda')
                ->select(
                    'mda_id',
                    DB::raw('count(*) as total')
                )
                ->groupBy('mda_id')
                ->orderByDesc('total')
                ->take(5)
                ->get();

            $partnerProjects = (clone $scopedProjects)->with('developmentPartner')
                ->select(
                    'development_partner_id',
                    DB::raw('count(*) as total')
                )
                ->groupBy('development_partner_id')
                ->orderByDesc('total')
                ->take(5)
                ->get();
        }



        /*
        |--------------------------------------------------------------------------
        | Recent Projects
        |--------------------------------------------------------------------------
        */

        $recentProjects = (clone $scopedProjects)->latest()
            ->take(5)
            ->get();

        $developmentPartnerNotice = null;

        if ($viewer->isDevelopmentPartner() && $viewer->development_partner_id === null) {
            $developmentPartnerNotice = 'Your development partner access has not been configured. A Super Administrator must assign your organisation before project information is available.';
        }

        $hideStatewideCounts = $viewer->isDevelopmentPartner() || $viewer->isMdaOfficer();
        $hideOrganisationSummaries = $viewer->isMdaOfficer();
        $dashboardHeading = $viewer->isMdaOfficer() ? 'My MDA Dashboard' : 'Executive Dashboard';
        $assignedMdaName = $viewer->isMdaOfficer() ? optional($viewer->mda)->name : null;
        $dashboardIntro = $viewer->isMdaOfficer()
            ? ($assignedMdaName
                ? 'Project monitoring for '.$assignedMdaName.'. Figures include only projects led by this MDA.'
                : 'Project monitoring for your assigned ministry, department or agency.')
            : 'Development Partners Project Monitoring and Evaluation Overview';

        $mdaOfficerNotice = null;

        if ($viewer->isMdaOfficer() && $viewer->mda_id === null) {
            $mdaOfficerNotice = 'Your MDA assignment has not been configured. A Super Administrator must assign your ministry before project information is available.';
        }

        return view('dashboard', compact(

            'totalProjects',
            'totalMDAs',
            'totalPartners',
            'totalMonitoring',

            'totalBudget',

            'planningProjects',
            'ongoingProjects',
            'completedProjects',
            'suspendedProjects',
            'cancelledProjects',

            'onTrackProjects',
            'attentionProjects',
            'lowProgressProjects',
            'registerDelayedProjects',

            'averageProgress',

            'recentMonitorings',

            'sectorLabels',
            'sectorTotals',

            'mdaProjects',

            'partnerProjects',

            'recentProjects',

            'developmentPartnerNotice',
            'mdaOfficerNotice',
            'hideStatewideCounts',
            'hideOrganisationSummaries',
            'dashboardHeading',
            'dashboardIntro',

        ));

    }
}