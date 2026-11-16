<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $reportsHeading }}</title>
    <style>
        body { font-family: Helvetica, sans-serif; font-size: 11px; color: #111827; }
        .banner { background: #15803d; color: #ffffff; padding: 14px 16px; margin: -8px -8px 16px; }
        .banner .eyebrow { font-size: 10px; letter-spacing: 0.04em; margin: 0 0 4px; }
        .banner h1 { font-size: 18px; margin: 0 0 2px; }
        .banner .system { font-size: 12px; margin: 0; }
        h2 { font-size: 14px; color: #14532d; margin: 0 0 8px; }
        p { margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #dcfce7; color: #14532d; }
        .summary td { width: 33%; text-align: center; font-size: 14px; font-weight: bold; }
        .meta { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="banner">
        <p class="eyebrow">Ondo State Government</p>
        <h1>Office of Development Partnerships</h1>
        <p class="system">ODPPMES — Ondo State Development Partners Programme Monitoring and Evaluation System</p>
    </div>

    <h2>{{ $reportsHeading }}</h2>
    <p class="meta">Report date: {{ $date }}</p>
    @if ($assignedMdaName)
        <p class="meta">MDA: {{ $assignedMdaName }}</p>
    @endif

    <p>
        Filters:
        @if ($filters === [])
            All projects
        @else
            @foreach ($filters as $label => $value)
                {{ $label }}: {{ $value }}@if (! $loop->last); @endif
            @endforeach
        @endif
    </p>

    <table class="summary">
        <tr>
            <th>Projects Found</th>
            <th>Average Progress</th>
            <th>Completed Projects</th>
        </tr>
        <tr>
            <td>{{ $summary['total'] }}</td>
            <td>{{ $summary['average'] }}%</td>
            <td>{{ $summary['completed'] }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Project</th>
                <th>MDA</th>
                <th>Partner</th>
                <th>Sector</th>
                <th>LGA</th>
                <th>Status</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($projects as $project)
                <tr>
                    <td>{{ $project->project_title }}</td>
                    <td>{{ $project->mda->name ?? '-' }}</td>
                    <td>{{ $project->developmentPartner->partner_name ?? '-' }}</td>
                    <td>{{ $project->sector }}</td>
                    <td>{{ $project->lga }}</td>
                    <td>{{ $project->status }}</td>
                    <td>{{ $project->progress }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
