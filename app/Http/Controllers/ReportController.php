<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Mda;
use App\Models\DevelopmentPartner;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display Reports Page
     */
    public function index(Request $request)
    {
        $projects = $this->filteredProjects($request);
        $user = $request->user();
        $lockMdaFilter = $user->isMdaOfficer();

        return view('reports.index', array_merge(
            $this->reportPageMeta($user),
            [
                'projects' => $projects,
                'summary' => $this->reportSummary($projects),
                'mdas' => $this->mdaFilterOptions($user),
                'partners' => $this->partnerFilterOptions($user),
                'lockMdaFilter' => $lockMdaFilter,
            ]
        ));
    }

    /**
     * Export Report to PDF
     */
    public function pdf(Request $request)
    {
        $user = $request->user();
        $projects = $this->filteredProjects($request);
        $filters = $this->reportFilters($request);
        $meta = $this->reportPageMeta($user);

        $pdf = Pdf::loadView('reports.pdf', [
            'projects' => $projects,
            'summary' => $this->reportSummary($projects),
            'date' => Carbon::now()->format('d F Y'),
            'filters' => $filters,
            'reportsHeading' => $meta['reportsHeading'],
            'assignedMdaName' => $meta['assignedMdaName'],
        ])->setPaper('a4', 'landscape');

        $filename = $user->isMdaOfficer()
            ? 'ODPPMES_My_Reports.pdf'
            : 'ODPPMES_Project_Report.pdf';

        return $pdf->download($filename);
    }

    private function filteredProjects(Request $request)
    {
        $user = $request->user();
        $query = Project::with(['mda', 'developmentPartner'])
            ->visibleTo($user);

        if ($user->isMdaOfficer()) {
            if ($user->mda_id === null) {
                return $query->whereRaw('0 = 1')->latest()->get();
            }

            $query->where('mda_id', $user->mda_id);
        } elseif ($request->filled('mda_id')) {
            $query->where('mda_id', $request->mda_id);
        }

        if ($request->filled('partner_id')) {
            $query->where('development_partner_id', $request->partner_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sector')) {
            $query->where('sector', $request->sector);
        }

        if ($request->filled('lga')) {
            $query->where('lga', $request->lga);
        }

        return $query->latest()->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Project>  $projects
     * @return array{total: int, average: float, completed: int}
     */
    private function reportSummary($projects): array
    {
        return [
            'total' => $projects->count(),
            'average' => round((float) ($projects->avg('progress') ?? 0), 1),
            'completed' => $projects->where('status', 'Completed')->count(),
        ];
    }

    /**
     * @return array{reportsHeading: string, reportsIntro: string, assignedMdaName: ?string}
     */
    private function reportPageMeta($user): array
    {
        $lockMdaFilter = $user->isMdaOfficer();
        $assignedMdaName = $lockMdaFilter
            ? (string) (optional($user->mda)->name ?: 'Assigned ministry')
            : null;

        return [
            'reportsHeading' => $lockMdaFilter ? 'My Reports' : 'Project Reports',
            'reportsIntro' => $lockMdaFilter
                ? 'Projects led by your assigned MDA. Figures are limited to that ministry.'
                : 'Filter, view and export project performance reports',
            'assignedMdaName' => $assignedMdaName,
        ];
    }

    private function mdaFilterOptions($user)
    {
        return Mda::query()
            ->when($user->isMdaOfficer(), fn ($query) => $query->whereKey($user->mda_id))
            ->orderBy('name')
            ->get();
    }

    private function partnerFilterOptions($user)
    {
        return DevelopmentPartner::query()
            ->when($user->isMdaOfficer(), function ($query) use ($user) {
                $ids = Project::query()
                    ->visibleTo($user)
                    ->whereNotNull('development_partner_id')
                    ->pluck('development_partner_id');

                $query->whereIn('id', $ids);
            })
            ->orderBy('partner_name')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    private function reportFilters(Request $request): array
    {
        $filters = [];

        if ($request->user()->isMdaOfficer()) {
            $filters['MDA'] = (string) (optional($request->user()->mda)->name ?: 'Assigned ministry');
        } elseif ($request->filled('mda_id')) {
            $filters['MDA'] = (string) (Mda::whereKey($request->mda_id)->value('name') ?: 'Selected ministry');
        }

        if ($request->filled('partner_id')) {
            $filters['Development Partner'] = (string) (DevelopmentPartner::whereKey($request->partner_id)->value('partner_name') ?: 'Selected partner');
        }

        if ($request->filled('status')) {
            $filters['Status'] = (string) $request->status;
        }

        if ($request->filled('sector')) {
            $filters['Sector'] = (string) $request->sector;
        }

        if ($request->filled('lga')) {
            $filters['LGA'] = (string) $request->lga;
        }

        return $filters;
    }
}