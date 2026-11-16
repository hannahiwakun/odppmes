<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Monitoring;
use App\Models\MonitoringEvidence;
use App\Support\OperationalStorage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MonitoringEvidenceController extends Controller
{
    public function store(Request $request, Monitoring $monitoring)
    {
        $monitoring->loadMissing('project');

        $request->validate([
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'required|file',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
        ]);

        $files = array_values($request->file('files', []));
        $captions = array_values($request->input('captions', []));
        $prepared = [];

        foreach ($files as $index => $file) {
            $prepared[] = $this->prepareUpload($file, $captions[$index] ?? null);
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use ($monitoring, $prepared, &$storedPaths) {
                foreach ($prepared as $item) {
                    $filename = Str::uuid()->toString().'.'.$item['extension'];
                    $path = $item['file']->storeAs(
                        MonitoringEvidence::DIRECTORY,
                        $filename,
                        OperationalStorage::DISK
                    );

                    if (! MonitoringEvidence::isSafePath($path)) {
                        throw ValidationException::withMessages([
                            'files' => 'The evidence file could not be stored safely.',
                        ]);
                    }

                    $storedPaths[] = $path;

                    $evidence = MonitoringEvidence::create([
                        'monitoring_id' => $monitoring->id,
                        'file_path' => $path,
                        'file_type' => $item['type'],
                        'original_filename' => $item['original'],
                        'mime_type' => $item['mime'],
                        'file_size' => $item['size'],
                        'caption' => $item['caption'],
                        'uploaded_by' => auth()->id(),
                    ]);

                    AuditLogger::log(
                        'Created',
                        'Monitoring Evidence',
                        'Uploaded '.$item['type'].' "'.$evidence->original_filename.'" for monitoring visit on project: '.$monitoring->project->project_title
                    );
                }
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                MonitoringEvidence::deleteStoredFile($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('monitorings.show', $monitoring)
            ->with('success', 'Monitoring evidence uploaded successfully.');
    }

    public function file(Monitoring $monitoring, MonitoringEvidence $monitoringEvidence)
    {
        $this->assertEvidenceBelongsToVisit($monitoring, $monitoringEvidence);
        $path = $this->safePath($monitoringEvidence);

        return OperationalStorage::disk()->response($path, $this->downloadName($monitoringEvidence->original_filename), [
            'Content-Type' => $monitoringEvidence->mime_type,
            'Content-Disposition' => 'inline; filename="'.$this->downloadName($monitoringEvidence->original_filename).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(Monitoring $monitoring, MonitoringEvidence $monitoringEvidence)
    {
        $this->assertEvidenceBelongsToVisit($monitoring, $monitoringEvidence);
        $path = $this->safePath($monitoringEvidence);

        return OperationalStorage::disk()->download(
            $path,
            $this->downloadName($monitoringEvidence->original_filename)
        );
    }

    public function destroy(Monitoring $monitoring, MonitoringEvidence $monitoringEvidence)
    {
        $this->abortUnlessEvidenceDeletionIsAllowed();
        $this->assertEvidenceBelongsToVisit($monitoring, $monitoringEvidence);

        $path = $monitoringEvidence->file_path;
        $name = $monitoringEvidence->original_filename;
        $projectTitle = $monitoring->project->project_title;

        DB::transaction(function () use ($monitoringEvidence, $name, $projectTitle) {
            AuditLogger::log(
                'Deleted',
                'Monitoring Evidence',
                'Deleted evidence "'.$name.'" from monitoring visit on project: '.$projectTitle
            );

            $monitoringEvidence->delete();
        });

        MonitoringEvidence::deleteStoredFile($path);

        return redirect()
            ->route('monitorings.show', $monitoring)
            ->with('success', 'Monitoring evidence deleted successfully.');
    }

    /**
     * @return array{file: UploadedFile, type: string, mime: string, extension: string, size: int, original: string, caption: ?string}
     */
    private function prepareUpload(UploadedFile $file, ?string $caption): array
    {
        $original = basename(str_replace('\\', '/', (string) $file->getClientOriginalName()));
        $detected = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: '';

        $photoExtension = MonitoringEvidence::PHOTO_MIME_TYPES[$detected] ?? null;
        $videoExtension = MonitoringEvidence::VIDEO_MIME_TYPES[$detected] ?? null;

        if ($photoExtension !== null) {
            $type = MonitoringEvidence::TYPE_PHOTO;
            $extension = $photoExtension;
            $maxKilobytes = MonitoringEvidence::PHOTO_MAX_KILOBYTES;
        } elseif ($videoExtension !== null) {
            $type = MonitoringEvidence::TYPE_VIDEO;
            $extension = $videoExtension;
            $maxKilobytes = MonitoringEvidence::VIDEO_MAX_KILOBYTES;
        } else {
            throw ValidationException::withMessages([
                'files' => 'Each file must be a JPEG, PNG or WebP photograph, or an MP4 video.',
            ]);
        }

        $sizeKilobytes = (int) ceil($file->getSize() / 1024);

        if ($sizeKilobytes > $maxKilobytes) {
            $limit = $type === MonitoringEvidence::TYPE_VIDEO ? '100 MB' : '5 MB';

            throw ValidationException::withMessages([
                'files' => 'Each '.$type.' must be '.$limit.' or smaller.',
            ]);
        }

        $caption = is_string($caption) ? trim($caption) : '';

        return [
            'file' => $file,
            'type' => $type,
            'mime' => $detected,
            'extension' => $extension,
            'size' => $file->getSize(),
            'original' => $original !== '' && $original !== '.' ? $original : 'evidence.'.$extension,
            'caption' => $caption !== '' ? $caption : null,
        ];
    }

    private function assertEvidenceBelongsToVisit(Monitoring $monitoring, MonitoringEvidence $evidence): void
    {
        if ((int) $evidence->monitoring_id !== (int) $monitoring->id) {
            abort(404);
        }
    }

    private function safePath(MonitoringEvidence $evidence): string
    {
        $path = $evidence->file_path;

        if (! MonitoringEvidence::isSafePath($path) || ! OperationalStorage::disk()->exists($path)) {
            abort(404);
        }

        return str_replace('\\', '/', $path);
    }

    private function downloadName(?string $fileName): string
    {
        $name = basename(str_replace('\\', '/', (string) $fileName));

        return $name !== '' && $name !== '.' ? $name : 'evidence';
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
