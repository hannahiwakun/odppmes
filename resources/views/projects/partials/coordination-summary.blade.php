<div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
    <h2 class="mb-2 text-xl font-bold text-green-900">Development register</h2>
    <p class="mb-6 text-sm text-gray-500">Figures below are what the MDA reported. A blank means the information has not been supplied.</p>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Nature</p>
            <p class="mt-2 font-bold">{{ $project->nature ?: '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Coverage</p>
            <p class="mt-2 font-bold">{{ $project->geographical_coverage ?: '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Register status</p>
            <p class="mt-2 font-bold">{{ $project->initiative_status ?: $project->status }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4 md:col-span-3">
            <p class="text-sm text-gray-500">Other participating MDAs</p>
            <p class="mt-2 font-bold">
                {{ $project->participatingMdas->pluck('name')->join(', ') ?: '-' }}
            </p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Approved value</p>
            <p class="mt-2 font-bold">{{ $project->approved_value !== null ? number_format((float) $project->approved_value, 2).' '.($project->funding_currency ?: '') : '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Funds drawn down</p>
            <p class="mt-2 font-bold">{{ $project->funds_drawn !== null ? number_format((float) $project->funds_drawn, 2) : '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Cumulative expenditure</p>
            <p class="mt-2 font-bold">{{ $project->cumulative_expenditure !== null ? number_format((float) $project->cumulative_expenditure, 2) : '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Counterpart required</p>
            <p class="mt-2 font-bold">{{ $project->counterpart_required !== null ? number_format((float) $project->counterpart_required, 2) : '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Counterpart paid</p>
            <p class="mt-2 font-bold">{{ $project->counterpart_paid !== null ? number_format((float) $project->counterpart_paid, 2) : '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Counterpart still unpaid</p>
            <p class="mt-2 font-bold">{{ $project->outstandingCounterpart() !== null ? number_format($project->outstandingCounterpart(), 2) : '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Direct beneficiaries</p>
            <p class="mt-2 font-bold">{{ $project->direct_beneficiaries ?: '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Expected outcomes</p>
            <p class="mt-2 font-bold">{{ $project->expected_outcomes ?: '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Programme officer</p>
            <p class="mt-2 font-bold">{{ $project->programme_officer ?: '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Record check</p>
            <p class="mt-2 font-bold">{{ $project->verification_status ?: 'Not yet reviewed' }}</p>
        </div>
    </div>
</div>
