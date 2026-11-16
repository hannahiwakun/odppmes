<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Project;
use App\Models\ProjectPhoto;
use App\Support\OperationalStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectPhotoController extends Controller
{

    /**
     * Display project photos
     */
    public function index(Project $project)
    {
        $photos = $project->photos()
            ->latest()
            ->paginate(12);

        return view('project-photos.index', compact(
            'project',
            'photos'
        ));
    }



    /**
     * Upload photo form
     */
    public function create(Project $project)
    {
        return view('project-photos.create', compact('project'));
    }



    /**
     * Store uploaded photo
     */
    public function store(Request $request, Project $project)
    {

        $request->validate([

            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120'
            ],

            'caption' => [
                'nullable',
                'string',
                'max:255'
            ],

        ]);



        $path = $request->file('photo')
            ->store('project_photos', OperationalStorage::DISK);



        ProjectPhoto::create([

            'project_id' => $project->id,

            'photo' => $path,

            'caption' => $request->caption,

            'uploaded_by' => auth()->id(),

        ]);



       return redirect()

    ->route('projects.photos.index', $project)

    ->with(
        'success',
        'Project photo uploaded successfully.'
    );

    }




    /**
     * Delete the photo identified by the route, only when it belongs to this project.
     */
    public function destroy(Project $project, ProjectPhoto $photo)
    {
        $this->abortUnlessEvidenceDeletionIsAllowed();

        if ((int) $photo->project_id !== (int) $project->id) {
            abort(404);
        }

        $path = str_replace('\\', '/', (string) $photo->photo);
        $label = trim((string) $photo->caption);
        if ($label === '') {
            $label = 'Project Photo';
        }

        DB::transaction(function () use ($photo, $project, $label) {
            AuditLogger::log(
                'Deleted',
                'Project Photo',
                'Deleted "'.$label.'" from project: '.$project->project_title
            );

            $photo->delete();
        });

        if (
            $path !== ''
            && ! str_contains($path, '..')
            && ! str_contains($path, "\0")
            && str_starts_with($path, 'project_photos/')
            && OperationalStorage::disk()->exists($path)
        ) {
            OperationalStorage::disk()->delete($path);
        }

        return redirect()
            ->route('projects.photos.index', $project)
            ->with(
                'success',
                'Photo deleted successfully.'
            );
    }

    /**
     * Show one project photo to an authorized user. The file stays where it was stored.
     */
    public function file(Project $project, ProjectPhoto $photo)
    {
        if ((int) $photo->project_id !== (int) $project->id) {
            abort(404);
        }

        $path = $this->photoPath($photo->photo);

        if ($path === null || ! OperationalStorage::disk()->exists($path)) {
            abort(404);
        }

        return OperationalStorage::disk()->response($path);
    }

    private function photoPath(?string $path): ?string
    {
        $path = str_replace('\\', '/', (string) $path);

        if (
            $path === ''
            || str_contains($path, '..')
            || str_contains($path, "\0")
            || ! str_starts_with($path, 'project_photos/')
        ) {
            return null;
        }

        return $path;
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