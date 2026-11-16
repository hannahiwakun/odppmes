<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\MonitoringEvidence;
use App\Models\Project;
use App\Helpers\AuditLogger;
use App\Support\OperationalStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class MonitoringController extends Controller
{
    public function index()
    {
        $monitorings = Monitoring::with(['project','user'])
            ->whereHas('project', fn ($query) => $query->visibleTo(auth()->user()))
            ->latest()
            ->paginate(10);

        return view('monitorings.index', compact('monitorings'));
    }

public function create(Request $request)
{
    $projects = Project::query()->visibleTo($request->user())->orderBy('project_title')->get();

    $selectedProject = $request->query('project');

    return view('monitorings.create', compact(
        'projects',
        'selectedProject'
    ));
}

    public function store(Request $request)
    {
        $request->validate($this->visitRules());

        $project = Project::findOrFail($request->project_id);
        abort_unless($request->user()->canAccessProject($project), 404);

        $photoPath = $this->storeUploadedPhoto($request);

        try {
            DB::transaction(function () use ($request, $photoPath) {
                $monitoring = Monitoring::create([
                    'project_id' => $request->project_id,
                    'user_id' => auth()->id(),
                    'monitoring_date' => $request->monitoring_date,
                    'progress_before' => $request->progress_before ?? 0,
                    'progress_after' => $request->progress_after ?? 0,
                    'status' => $request->status,
                    'findings' => $request->findings,
                    'challenges' => $request->challenges,
                    'recommendations' => $request->recommendations,
                    'photo' => $photoPath,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);

                $monitoring->project->update([
                    'progress' => $request->progress_after ?? 0,
                    'status' => $request->status,
                ]);

                AuditLogger::log(
                    'Created',
                    'Monitoring',
                    'Created monitoring record for project: '.$monitoring->project->project_title
                );
            });
        } catch (Throwable $exception) {
            $this->deleteMonitoringPhoto($photoPath);

            throw $exception;
        }

        return redirect()
            ->route('monitorings.index')
            ->with('success','Monitoring record created successfully.');
    }


    public function show(Monitoring $monitoring)
    {
        $monitoring->load(['project', 'user', 'evidence.uploader']);

        return view('monitorings.show', compact('monitoring'));
    }

    /**
     * Show the visit photo to an authorized user. The file stays where it was stored.
     */
    public function photo(Monitoring $monitoring)
    {
        $path = str_replace('\\', '/', (string) $monitoring->photo);

        if (! $this->isLegacyVisitPhotoPath($path) || ! OperationalStorage::disk()->exists($path)) {
            abort(404);
        }

        return OperationalStorage::disk()->response($path);
    }


    public function edit(Monitoring $monitoring)
    {
        $projects = Project::query()->visibleTo(auth()->user())->orderBy('project_title')->get();

        return view('monitorings.edit', compact('monitoring','projects'));
    }


    public function update(Request $request, Monitoring $monitoring)
    {
        $request->validate($this->visitRules());

        $target = Project::findOrFail($request->project_id);
        abort_unless($request->user()->canAccessProject($target), 404);
        abort_unless($request->user()->canAccessProject($monitoring->project), 404);

        $previousPhoto = $monitoring->photo;
        $newPhotoPath = $this->storeUploadedPhoto($request);

        try {
            DB::transaction(function () use ($request, $monitoring, $previousPhoto, $newPhotoPath) {
                $monitoring->update([
                    'project_id' => $request->project_id,
                    'monitoring_date' => $request->monitoring_date,
                    'progress_before' => $request->progress_before ?? 0,
                    'progress_after' => $request->progress_after ?? 0,
                    'status' => $request->status,
                    'findings' => $request->findings,
                    'challenges' => $request->challenges,
                    'recommendations' => $request->recommendations,
                    'photo' => $newPhotoPath ?? $previousPhoto,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);

                // The project relation was loaded for the access check. Updating
                // that same record preserves the existing parent-project rule.
                $monitoring->project->update([
                    'progress' => $request->progress_after ?? 0,
                    'status' => $request->status,
                ]);

                AuditLogger::log(
                    'Updated',
                    'Monitoring',
                    'Updated monitoring record for project: '.$monitoring->project->project_title
                );
            });
        } catch (Throwable $exception) {
            $this->deleteMonitoringPhoto($newPhotoPath);

            throw $exception;
        }

        if ($newPhotoPath !== null && $newPhotoPath !== $previousPhoto) {
            $this->deleteMonitoringPhoto($previousPhoto);
        }

        return redirect()
            ->route('monitorings.index')
            ->with('success','Monitoring updated successfully.');
    }


    public function destroy(Monitoring $monitoring)
    {
        $this->abortUnlessEvidenceDeletionIsAllowed();

        $photo = $monitoring->photo;
        $evidencePaths = $monitoring->evidence()->pluck('file_path')->all();
        $projectTitle = $monitoring->project->project_title;

        DB::transaction(function () use ($monitoring, $projectTitle) {
            AuditLogger::log(
                'Deleted',
                'Monitoring',
                'Deleted monitoring record for project: '.$projectTitle
            );

            $monitoring->evidence()->delete();
            $monitoring->delete();
        });

        foreach ($evidencePaths as $path) {
            MonitoringEvidence::deleteStoredFile($path);
        }

        $this->deleteMonitoringPhoto($photo);

        return redirect()
            ->route('monitorings.index')
            ->with('success','Monitoring deleted successfully.');
    }

    /**
     * @return array<string, string>
     */
    private function visitRules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'monitoring_date' => 'required|date',
            'status' => 'required|in:Planning,Ongoing,Completed,Suspended,Cancelled',
            'progress_before' => 'nullable|integer|min:0|max:100',
            'progress_after' => 'nullable|integer|min:0|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }

    private function storeUploadedPhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('monitorings', OperationalStorage::DISK);
    }

    /**
     * Remove one monitoring photograph. Paths outside that folder are left untouched.
     */
    private function deleteMonitoringPhoto(?string $path): void
    {
        $path = str_replace('\\', '/', (string) $path);

        if (! $this->isLegacyVisitPhotoPath($path)) {
            return;
        }

        $disk = OperationalStorage::disk();

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    private function isLegacyVisitPhotoPath(string $path): bool
    {
        return $path !== ''
            && ! str_contains($path, '..')
            && ! str_contains($path, "\0")
            && str_starts_with($path, 'monitorings/')
            && ! str_starts_with($path, MonitoringEvidence::DIRECTORY.'/')
            && substr_count($path, '/') === 1;
    }

    private function abortUnlessEvidenceDeletionIsAllowed(): void
    {
        $user = auth()->user();

        abort_unless(
            $user !== null && ($user->isSuperAdmin() || $user->isMEO()),
            403
        );
    }
}