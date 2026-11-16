<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Helpers\AuditLogger;
use App\Support\OperationalStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectDocumentController extends Controller
{
    /**
     * Display all documents for a project.
     */
    public function index(Project $project)
    {
        $documents = $project->documents()
            ->with('uploader')
            ->latest()
            ->paginate(10);

        return view('project-documents.index', compact('project', 'documents'));
    }

    /**
     * Show upload form.
     */
    public function create(Project $project)
    {
        return view('project-documents.create', compact('project'));
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|string|max:100',
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'remarks' => 'nullable|string',
        ]);

        $file = $request->file('document');

        $path = $file->store('project_documents', OperationalStorage::DISK);

        $document = ProjectDocument::create([
            'project_id'    => $project->id,
            'title'         => $request->title,
            'document_type' => $request->document_type,
            'file_name'     => $file->getClientOriginalName(),
            'file_path'     => $path,
            'uploaded_by'   => Auth::id(),
            'remarks'       => $request->remarks,
        ]);

        AuditLogger::log(
            'Created',
            'Project Document',
            'Uploaded document "' . $document->title . '" for project: ' . $project->project_title
        );

        return redirect()
            ->route('project-documents.index', $project)
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Download a document that belongs to the project in the URL.
     */
    public function download(Project $project, ProjectDocument $projectDocument)
    {
        $this->assertDocumentBelongsToProject($project, $projectDocument);
        $path = $this->safeDocumentPath($projectDocument->file_path);

        if (! OperationalStorage::disk()->exists($path)) {
            abort(404);
        }

        AuditLogger::log(
            'Downloaded',
            'Project Document',
            'Downloaded "' . $projectDocument->title . '" for project: ' . $project->project_title
        );

        return OperationalStorage::disk()->download(
            $path,
            $this->downloadName($projectDocument->file_name)
        );
    }

    /**
     * Delete a document that belongs to the project in the URL.
     */
    public function destroy(Project $project, ProjectDocument $projectDocument)
    {
        $this->abortUnlessEvidenceDeletionIsAllowed();
        $this->assertDocumentBelongsToProject($project, $projectDocument);
        $path = $this->safeDocumentPath($projectDocument->file_path);
        $title = $projectDocument->title;
        $projectTitle = $project->project_title;

        DB::transaction(function () use ($projectDocument, $title, $projectTitle) {
            AuditLogger::log(
                'Deleted',
                'Project Document',
                'Deleted "' . $title . '" from project: ' . $projectTitle
            );

            $projectDocument->delete();
        });

        if (OperationalStorage::disk()->exists($path)) {
            OperationalStorage::disk()->delete($path);
        }

        return redirect()
            ->route('project-documents.index', $project)
            ->with('success', 'Document deleted successfully.');
    }

    private function assertDocumentBelongsToProject(Project $project, ProjectDocument $projectDocument): void
    {
        if ((int) $projectDocument->project_id !== (int) $project->id) {
            abort(404);
        }
    }

    private function safeDocumentPath(?string $path): string
    {
        $path = str_replace('\\', '/', (string) $path);

        if (
            $path === ''
            || str_contains($path, '..')
            || str_contains($path, "\0")
            || ! str_starts_with($path, 'project_documents/')
        ) {
            abort(404);
        }

        return $path;
    }

    private function downloadName(?string $fileName): string
    {
        $name = basename(str_replace('\\', '/', (string) $fileName));

        return $name !== '' && $name !== '.' ? $name : 'document';
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
