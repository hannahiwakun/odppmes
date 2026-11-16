<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Mda;
use App\Models\DevelopmentPartner;
use App\Models\User;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display projects
     */
    public function index()
    {
        $projects = Project::with(['mda', 'developmentPartner'])
            ->visibleTo(auth()->user())
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }


    /**
     * Create project form
     */
    public function create()
    {
        Gate::authorize('create', Project::class);

        $mdas = $this->mdaOptionsForForm(auth()->user(), true);

        $developmentPartners = DevelopmentPartner::where('status', 1)
            ->orderBy('partner_name')
            ->get();

        $lockLeadMda = auth()->user()->isMdaOfficer();
        $assignedMda = $lockLeadMda ? auth()->user()->mda : null;

        return view('projects.create', compact(
            'mdas',
            'developmentPartners',
            'lockLeadMda',
            'assignedMda'
        ));
    }


    /**
     * Store project
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Project::class);

        $validated = $request->validate(array_merge([
            'project_title' => 'required|string|max:255',
            'project_code' => 'nullable|string|max:255',
            'mda_id' => $this->leadMdaRule($request->user()),
            'development_partner_id' => 'nullable|exists:development_partners,id',
            'sector' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'ward' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'funding_source' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|in:Planning,Ongoing,Completed,Suspended,Cancelled',
            'initiative_status' => 'nullable|in:Proposed,Under negotiation,Approved,Awaiting effectiveness,Planning,Ongoing,Delayed,Suspended,Completed,Cancelled',
            'progress' => 'nullable|integer|min:0|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
        ], $this->coordinationRules()));

        $attributes = $this->projectAttributes($request, $validated);
        $attributes['budget'] = $attributes['budget'] ?? 0;
        $attributes['status'] = $attributes['status'] ?? 'Planning';
        $attributes['progress'] = $attributes['progress'] ?? 0;
        $attributes['mda_id'] = $this->resolvedLeadMdaId($request->user(), $attributes['mda_id'] ?? null);

        $project = Project::create($attributes);
        $this->syncParticipatingMdas($project, $request);


        AuditLogger::log(
            'Created',
            'Project',
            'Created project: '.$project->project_title
        );


        return redirect()
            ->route('projects.index')
            ->with('success','Project created successfully.');
    }


    /**
     * Show project
     */
    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        $project->load('participatingMdas');

        return view('projects.show', compact('project'));
    }


    /**
     * Edit project
     */
    public function edit(Project $project)
    {
        Gate::authorize('update', $project);

        $mdas = $this->mdaOptionsForForm(auth()->user());

        $developmentPartners = DevelopmentPartner::orderBy('partner_name')->get();

        $project->load('participatingMdas');

        $lockLeadMda = auth()->user()->isMdaOfficer();
        $assignedMda = $lockLeadMda ? auth()->user()->mda : $project->mda;

        return view('projects.edit', compact(
            'project',
            'mdas',
            'developmentPartners',
            'lockLeadMda',
            'assignedMda'
        ));
    }


    /**
     * Update project
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate(array_merge([
            'project_title' => 'required|string|max:255',
            'project_code' => 'nullable|string|max:255',
            'mda_id' => $this->leadMdaRule($request->user()),
            'development_partner_id' => 'nullable|exists:development_partners,id',
            'sector' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'ward' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'funding_source' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => [
                'nullable',
                'date',
                Rule::when($request->filled('start_date'), ['after_or_equal:start_date']),
            ],
            'status' => 'required_without:initiative_status|nullable|in:Planning,Ongoing,Completed,Suspended,Cancelled',
            'initiative_status' => 'required_without:status|nullable|in:Proposed,Under negotiation,Approved,Awaiting effectiveness,Planning,Ongoing,Delayed,Suspended,Completed,Cancelled',
            'progress' => 'required|integer|min:0|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
        ], $this->coordinationRules()));

        Gate::authorize('update', $project);

        $attributes = $this->projectAttributes($request, $validated);
        $attributes['mda_id'] = $this->resolvedLeadMdaId(
            $request->user(),
            $attributes['mda_id'] ?? $project->mda_id
        );

        if (! Gate::allows('updateVerification', $project)) {
            unset($attributes['verification_status']);
        }

        if (array_key_exists('budget', $attributes)) {
            $attributes['budget'] = $attributes['budget'] ?? 0;
        }

        $project->update($attributes);
        $this->syncParticipatingMdas($project, $request);


        AuditLogger::log(
            'Updated',
            'Project',
            'Updated project: '.$project->project_title
        );


        return redirect()
            ->route('projects.index')
            ->with('success','Project updated successfully.');
    }


    /**
     * Delete project
     */
    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $blocked = [];

        if ($project->monitorings()->exists()) {
            $blocked[] = 'monitoring records';
        }

        if ($project->documents()->exists()) {
            $blocked[] = 'documents';
        }

        if ($project->photos()->exists()) {
            $blocked[] = 'photos';
        }

        if ($blocked !== []) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Project was not deleted because it still has '.implode(', ', $blocked).'.');
        }

        AuditLogger::log(
            'Deleted',
            'Project',
            'Deleted project: '.$project->project_title
        );

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success','Project deleted successfully.');
    }

    /**
     * @return array<int, string>|string
     */
    private function leadMdaRule(User $user): array|string
    {
        if ($user->isMdaOfficer()) {
            return 'nullable';
        }

        return 'required|exists:mdas,id';
    }

    private function resolvedLeadMdaId(User $user, mixed $submitted): int
    {
        if ($user->isMdaOfficer()) {
            abort_if($user->mda_id === null, 403);

            return (int) $user->mda_id;
        }

        return (int) $submitted;
    }

    private function mdaOptionsForForm(User $user, bool $activeOnly = false)
    {
        return Mda::query()
            ->when($user->isMdaOfficer(), fn ($query) => $query->whereKey($user->mda_id))
            ->when($activeOnly && ! $user->isMdaOfficer(), fn ($query) => $query->where('status', 1))
            ->orderBy('name')
            ->get();
    }

    private function coordinationRules(): array
    {
        return [
            'nature' => 'nullable|string|max:255',
            'geographical_coverage' => 'nullable|string|max:255',
            'objectives' => 'nullable|string',
            'key_activities' => 'nullable|string',
            'state_priority' => 'nullable|string|max:255',
            'funding_institution' => 'nullable|string|max:255',
            'assistance_nature' => 'nullable|string|max:255',
            'funding_arrangement' => 'nullable|string|max:255',
            'agreement_date' => 'nullable|date',
            'funding_currency' => 'nullable|string|max:10',
            'approved_value' => 'nullable|numeric|min:0',
            'partner_contribution' => 'nullable|numeric|min:0',
            'federal_contribution' => 'nullable|numeric|min:0',
            'state_contribution' => 'nullable|numeric|min:0',
            'other_contributions' => 'nullable|numeric|min:0',
            'counterpart_required' => 'nullable|numeric|min:0',
            'counterpart_paid' => 'nullable|numeric|min:0',
            'funds_drawn' => 'nullable|numeric|min:0',
            'cumulative_expenditure' => 'nullable|numeric|min:0',
            'exchange_rate' => 'nullable|numeric|min:0',
            'financial_as_of' => 'nullable|date',
            'financial_source' => 'nullable|string|max:255',
            'direct_beneficiaries' => 'nullable|string|max:255',
            'indirect_beneficiaries' => 'nullable|string|max:255',
            'beneficiary_categories' => 'nullable|string|max:255',
            'expected_outcomes' => 'nullable|string',
            'programme_officer' => 'nullable|string|max:255',
            'programme_officer_designation' => 'nullable|string|max:255',
            'programme_officer_phone' => 'nullable|string|max:50',
            'programme_officer_email' => 'nullable|email|max:255',
            'verification_status' => 'nullable|in:Reported,Partially verified,Verified,Awaiting clarification',
            'return_submitted_on' => 'nullable|date',
            'participating_mda_ids' => 'nullable|array',
            'participating_mda_ids.*' => 'integer|exists:mdas,id',
        ];
    }

    private function projectAttributes(Request $request, array $validated): array
    {
        unset($validated['participating_mda_ids']);

        if (! $request->user()->isSuperAdmin() && ! $request->user()->isMEO()) {
            unset($validated['verification_status']);
        }

        if (! empty($validated['initiative_status'])) {
            $validated['status'] = $this->legacyStatus($validated['initiative_status']);
        }

        if ($request->has('coordination_form')) {
            $validated['beneficiaries_are_estimates'] = $request->boolean('beneficiaries_are_estimates');
        }

        return $validated;
    }

    private function syncParticipatingMdas(Project $project, Request $request): void
    {
        if (! $request->has('coordination_form')) {
            return;
        }

        $leadId = (int) $project->mda_id;

        $ids = collect($request->input('participating_mda_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== $leadId)
            ->unique()
            ->values()
            ->all();

        $project->participatingMdas()->sync($ids);
    }

    private function legacyStatus(string $initiativeStatus): string
    {
        return match ($initiativeStatus) {
            'Ongoing', 'Delayed' => 'Ongoing',
            'Completed' => 'Completed',
            'Suspended' => 'Suspended',
            'Cancelled' => 'Cancelled',
            default => 'Planning',
        };
    }
}