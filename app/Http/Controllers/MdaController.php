<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Mda;
use App\Models\Monitoring;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class MdaController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Mda::class);

        $mdas = Mda::withCount(['projects', 'officers'])
            ->latest()
            ->paginate(10);

        return view('mdas.index', compact('mdas'));
    }

    public function create()
    {
        $this->authorize('create', Mda::class);

        return view('mdas.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Mda::class);

        $validated = $request->validate($this->mdaRules());

        DB::transaction(function () use ($validated) {
            $mda = Mda::create($validated);
            $mda->refresh();

            $this->writeAudit(
                'Created',
                $mda,
                'Status: '.$this->statusLabel($mda->status).'.'
            );
        });

        return redirect()
            ->route('mdas.index')
            ->with('success', 'MDA created successfully.');
    }

    public function show(Mda $mda)
    {
        $this->authorize('view', $mda);

        return $this->profileView($mda, false);
    }

    public function mine()
    {
        abort_unless(auth()->user()->isMdaOfficer(), 403);

        $mdaId = auth()->user()->mda_id;

        if ($mdaId === null) {
            return view('mdas.unassigned');
        }

        $mda = Mda::query()->findOrFail($mdaId);

        return $this->profileView($mda, true);
    }

    public function edit(Mda $mda)
    {
        $this->authorize('update', $mda);

        return view('mdas.edit', compact('mda'));
    }

    public function update(Request $request, Mda $mda)
    {
        $this->authorize('update', $mda);

        $validated = $request->validate($this->mdaRules($mda->id));

        DB::transaction(function () use ($mda, $validated) {
            $before = $mda->only(array_keys($validated));
            $mda->update($validated);

            $this->writeAudit('Updated', $mda, $this->changeDetails($before, $validated));
        });

        return redirect()
            ->route('mdas.index')
            ->with('success', 'MDA updated successfully.');
    }

    public function deactivate(Mda $mda)
    {
        $this->authorize('deactivate', $mda);

        DB::transaction(function () use ($mda) {
            $before = $mda->status;
            $mda->update(['status' => false]);

            $this->writeAudit(
                'Deactivated',
                $mda,
                'Status: '.$this->statusLabel($before).' -> Inactive.'
            );
        });

        return redirect()
            ->route('mdas.index')
            ->with('success', 'MDA deactivated. Its projects, reports, documents, and monitoring records were kept.');
    }

    public function destroy(Mda $mda)
    {
        $this->authorize('delete', $mda);

        $blocked = [];

        if ($mda->projects()->exists()) {
            $blocked[] = 'lead projects';
        }

        if ($mda->participatingProjects()->exists()) {
            $blocked[] = 'participation on other projects';
        }

        if ($blocked !== []) {
            return redirect()
                ->route('mdas.index')
                ->with('error', 'This MDA was not deleted because it still has '.implode(' and ', $blocked).'. Deactivate it if it should no longer be used. Related projects and records were kept.');
        }

        DB::transaction(function () use ($mda) {
            $this->writeAudit('Deleted', $mda, 'The ministry record was removed.');
            $mda->delete();
        });

        return redirect()
            ->route('mdas.index')
            ->with('success', 'MDA deleted successfully.');
    }

    private function profileView(Mda $mda, bool $ownProfile)
    {
        $mda->loadCount(['projects', 'officers']);

        $officers = $mda->officers()->orderBy('name')->get();

        $portfolio = $mda->projects()
            ->with('developmentPartner')
            ->latest()
            ->get();

        $recentMonitorings = Monitoring::query()
            ->with(['project', 'user'])
            ->whereHas('project', fn ($query) => $query->where('mda_id', $mda->id))
            ->latest()
            ->take(8)
            ->get();

        $active = $portfolio->whereIn('status', ['Planning', 'Ongoing', 'Suspended']);

        $performance = [
            'total' => $portfolio->count(),
            'ongoing' => $portfolio->where('status', 'Ongoing')->count(),
            'completed' => $portfolio->where('status', 'Completed')->count(),
            'delayed' => $portfolio->where('initiative_status', 'Delayed')->count(),
            'averageProgress' => round((float) ($portfolio->avg('progress') ?? 0), 1),
            'monitoringVisits' => Monitoring::query()
                ->whereHas('project', fn ($query) => $query->where('mda_id', $mda->id))
                ->count(),
            'onTrack' => $active->where('progress', '>=', 70)->count(),
        ];

        $canManage = auth()->user()->can('update', $mda);

        return view('mdas.show', compact(
            'mda',
            'officers',
            'portfolio',
            'recentMonitorings',
            'performance',
            'canManage',
            'ownProfile'
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function mdaRules(?int $ignoreId = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mdas', 'code')->ignore($ignoreId),
            ],
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'contact_person' => 'required|string|max:255',
            'address' => 'required|string',
            'status' => 'required|boolean',
            'focal_name' => 'required|string|max:255',
            'focal_designation' => 'required|string|max:255',
            'focal_department' => 'required|string|max:255',
            'focal_phone' => 'required|string|max:50',
            'focal_email' => 'required|email|max:255',
            'focal_supervisor' => 'required|string|max:255',
            'alternate_name' => 'nullable|string|max:255',
            'alternate_phone' => 'nullable|string|max:50',
            'alternate_email' => 'nullable|email|max:255',
        ];
    }

    private function writeAudit(string $action, Mda $mda, string $details): void
    {
        $user = auth()->user();

        if ($user === null) {
            throw new RuntimeException('MDA audit log requires an authenticated user.');
        }

        AuditLogger::log(
            $action,
            'MDA',
            'MDA #'.$mda->id.' '.$mda->name.' ('.$mda->code.'). Actor user #'.$user->id.', role '.$user->role.'. '.$details
        );
    }

    private function changeDetails(array $before, array $after): string
    {
        $parts = [];

        foreach ($after as $field => $newValue) {
            $oldValue = $before[$field] ?? null;

            if ($this->sameValue($field, $oldValue, $newValue)) {
                continue;
            }

            $label = $this->fieldLabel($field);

            if (! $this->showsValue($field)) {
                $parts[] = $label.' changed';
                continue;
            }

            $parts[] = $label.': '.$this->displayValue($field, $oldValue).' -> '.$this->displayValue($field, $newValue);
        }

        return $parts === [] ? 'No field values changed.' : implode('; ', $parts).'.';
    }

    private function sameValue(string $field, mixed $oldValue, mixed $newValue): bool
    {
        if ($field === 'status') {
            return $this->statusLabel($oldValue) === $this->statusLabel($newValue);
        }

        return $this->plainText($oldValue) === $this->plainText($newValue);
    }

    private function displayValue(string $field, mixed $value): string
    {
        if ($field === 'status') {
            return $this->statusLabel($value);
        }

        $text = $this->plainText($value);

        return $text === '' ? 'empty' : $text;
    }

    private function statusLabel(mixed $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Active' : 'Inactive';
    }

    private function plainText(mixed $value): string
    {
        return $value === null ? '' : trim((string) $value);
    }

    private function showsValue(string $field): bool
    {
        return in_array($field, ['name', 'code', 'status', 'focal_department', 'focal_designation'], true);
    }

    private function fieldLabel(string $field): string
    {
        return [
            'name' => 'Name',
            'code' => 'Code',
            'email' => 'Email',
            'phone' => 'Phone',
            'contact_person' => 'Contact person',
            'address' => 'Address',
            'status' => 'Status',
            'focal_name' => 'Focal person',
            'focal_designation' => 'Focal designation',
            'focal_department' => 'Department',
            'focal_phone' => 'Focal person phone',
            'focal_email' => 'Focal person email',
            'focal_supervisor' => 'Supervising director',
            'alternate_name' => 'Alternate officer',
            'alternate_phone' => 'Alternate officer phone',
            'alternate_email' => 'Alternate officer email',
        ][$field] ?? 'Field';
    }
}
