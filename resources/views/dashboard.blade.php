@extends('layouts.admin')

@section('title', $dashboardHeading)

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-green-900">{{ $dashboardHeading }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $dashboardIntro }}</p>
    </div>

    @if($developmentPartnerNotice)
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-950" role="status">
            {{ $developmentPartnerNotice }}
        </div>
    @elseif($mdaOfficerNotice)
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-950" role="status">
            {{ $mdaOfficerNotice }}
        </div>
    @else
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Total Projects</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $totalProjects }}</h2>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Total Project Budget</p>
            <h2 class="mt-3 text-3xl font-bold text-green-900">₦{{ number_format($totalBudget, 2) }}</h2>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Ongoing Projects</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $ongoingProjects }}</h2>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Completed Projects</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $completedProjects }}</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        @unless($hideStatewideCounts)
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">MDAs (statewide)</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $totalMDAs }}</h2>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Development Partners (statewide)</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $totalPartners }}</h2>
        </div>
        @endunless

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Monitoring Visits</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $totalMonitoring }}</h2>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Average Progress</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $averageProgress }}%</h2>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-200"
                 role="progressbar"
                 aria-valuenow="{{ min(max((float) $averageProgress, 0), 100) }}"
                 aria-valuemin="0"
                 aria-valuemax="100"
                 aria-label="Average progress">
                <div class="h-2 rounded-full bg-green-700" style="width: {{ min(max((float) $averageProgress, 0), 100) }}%"></div>
            </div>
        </div>
    </div>

    <div>
        <h2 class="mb-4 text-xl font-bold text-green-900">Project Performance Overview</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
                <p class="text-sm text-gray-600">On Track Projects</p>
                <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $onTrackProjects }}</h2>
                <p class="mt-2 text-sm text-gray-600">Planning, Ongoing, or Suspended projects with progress of 70% or above.</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
                <p class="text-sm text-gray-600">Needs Attention</p>
                <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $attentionProjects }}</h2>
                <p class="mt-2 text-sm text-gray-600">Planning, Ongoing, or Suspended projects with progress from 30% to 69%.</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
                <p class="text-sm text-gray-600">Low Progress (Below 30%)</p>
                <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $lowProgressProjects }}</h2>
                <p class="mt-2 text-sm text-gray-600">Planning, Ongoing, or Suspended projects with progress below 30%.</p>
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-gray-200 bg-white p-6 shadow">
            <p class="text-sm text-gray-600">Register Status: Delayed</p>
            <h2 class="mt-3 text-4xl font-bold text-green-900">{{ $registerDelayedProjects }}</h2>
            <p class="mt-2 text-sm text-gray-600">This counts the development-register field, including projects whose current status is Completed or Cancelled. A monitoring visit may update the project status separately.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-bold text-green-900">Project Status Summary</h2>
            <canvas id="statusChart"></canvas>
            <ul class="mt-4 space-y-2 text-sm text-gray-700">
                <li class="flex justify-between gap-4 border-b border-gray-200 pb-2"><span>Planning</span><span class="font-semibold text-gray-900">{{ $planningProjects }}</span></li>
                <li class="flex justify-between gap-4 border-b border-gray-200 pb-2"><span>Ongoing</span><span class="font-semibold text-gray-900">{{ $ongoingProjects }}</span></li>
                <li class="flex justify-between gap-4 border-b border-gray-200 pb-2"><span>Completed</span><span class="font-semibold text-gray-900">{{ $completedProjects }}</span></li>
                <li class="flex justify-between gap-4 border-b border-gray-200 pb-2"><span>Suspended</span><span class="font-semibold text-gray-900">{{ $suspendedProjects }}</span></li>
                <li class="flex justify-between gap-4"><span>Cancelled</span><span class="font-semibold text-gray-900">{{ $cancelledProjects }}</span></li>
            </ul>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-bold text-green-900">Project Sector Distribution</h2>
            <canvas id="sectorChart"></canvas>
        </div>
    </div>

    @unless($hideOrganisationSummaries)
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-bold text-green-900">Top Implementing MDAs</h2>
            <div class="space-y-4">
                @forelse($mdaProjects as $mda)
                    <div class="flex items-center justify-between gap-4 border-b border-gray-200 pb-3">
                        <span class="font-medium text-gray-700">{{ $mda->mda->name ?? 'Unknown MDA' }}</span>
                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">{{ $mda->total }} Projects</span>
                    </div>
                @empty
                    <p class="text-gray-500">No MDA data available.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-bold text-green-900">Development Partner Summary</h2>
            <div class="space-y-4">
                @forelse($partnerProjects as $partner)
                    <div class="flex items-center justify-between gap-4 border-b border-gray-200 pb-3">
                        <span class="font-medium text-gray-700">{{ $partner->developmentPartner->partner_name ?? 'Unknown Partner' }}</span>
                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">{{ $partner->total }} Projects</span>
                    </div>
                @empty
                    <p class="text-gray-500">No development partner data available.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endunless

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h2 class="mb-4 text-xl font-bold text-green-900">Recent Monitoring Activities</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-green-700 text-white">
                    <tr>
                        <th class="px-3 py-3 text-left text-sm font-semibold" scope="col">Date</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold" scope="col">Project</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold" scope="col">Officer</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold" scope="col">Status</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold" scope="col">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentMonitorings as $monitoring)
                        @php
                            $statusBadge = match ($monitoring->status) {
                                'Planning' => 'bg-gray-100 text-gray-700',
                                'Ongoing' => 'bg-green-100 text-green-800',
                                'Completed' => 'bg-green-200 text-green-900',
                                'Suspended' => 'bg-amber-100 text-amber-900',
                                'Cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-3 py-3 text-sm">{{ \Carbon\Carbon::parse($monitoring->monitoring_date)->format('d M Y') }}</td>
                            <td class="px-3 py-3 text-sm">{{ $monitoring->project->project_title ?? '-' }}</td>
                            <td class="px-3 py-3 text-sm">{{ $monitoring->user->name ?? '-' }}</td>
                            <td class="px-3 py-3 text-sm">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">{{ $monitoring->status }}</span>
                            </td>
                            <td class="px-3 py-3 text-sm font-semibold text-green-800">{{ $monitoring->progress_after }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-gray-500">No monitoring activities found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @endif
</div>

@unless($developmentPartnerNotice || $mdaOfficerNotice)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

new Chart(document.getElementById('statusChart'), {

    type: 'bar',

    data: {

        labels: [
            'Planning',
            'Ongoing',
            'Completed',
            'Suspended',
            'Cancelled'
        ],

        datasets: [{

            label: 'Projects',

            data: [

                {{ $planningProjects }},
                {{ $ongoingProjects }},
                {{ $completedProjects }},
                {{ $suspendedProjects }},
                {{ $cancelledProjects }}

            ],

            backgroundColor: [

                '#166534',
                '#22c55e',
                '#15803d',
                '#eab308',
                '#dc2626'

            ]

        }]

    },

    options: {

        responsive:true,

        plugins:{
            legend:{
                display:false
            }
        }

    }

});

new Chart(document.getElementById('sectorChart'), {

    type:'doughnut',

    data:{

        labels:{!! json_encode($sectorLabels) !!},

        datasets:[{

            data:{!! json_encode($sectorTotals) !!},

            backgroundColor:[

                '#166534',
                '#22c55e',
                '#eab308',
                '#2563eb',
                '#dc2626'

            ]

        }]

    },

    options:{

        responsive:true,

        plugins:{

            legend:{

                position:'bottom'

            }

        }

    }

});

</script>
@endunless

@endsection
