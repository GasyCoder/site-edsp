<?php

namespace App\Services;

use App\Actions\GenerateApplicationNumber;
use App\Jobs\SendApplicationConfirmation;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class ApplicationService
{
    public function __construct(
        private readonly GenerateApplicationNumber $generateNumber,
        private readonly ActivityLogger $activities,
    ) {}

    public function create(array $data): Application
    {
        $documents = $data['documents'] ?? [];
        unset($data['documents']);
        $storedPaths = [];

        try {
            $application = DB::transaction(function () use ($data, $documents, &$storedPaths): Application {
                $data['public_id'] = (string) Str::uuid();
                $data['application_number'] = $this->number();
                $data['status'] = 'submitted';
                $data['submitted_at'] = now();

                $application = Application::query()->create($data);
                ApplicationStatusHistory::query()->create([
                    'application_id' => $application->id,
                    'old_status' => null,
                    'new_status' => 'submitted',
                    'comment' => 'Dossier soumis en ligne.',
                ]);

                foreach ($documents as $type => $file) {
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }
                    $extension = Str::lower($file->getClientOriginalExtension());
                    $path = 'applications/'.$application->public_id.'/'.Str::uuid().'.'.$extension;
                    $storedPath = Storage::disk('private')->putFileAs(dirname($path), $file, basename($path));

                    if ($storedPath === false) {
                        throw new RuntimeException('Le document candidat n’a pas pu être stocké.');
                    }

                    $storedPaths[] = $storedPath;
                    $application->documents()->create([
                        'type' => is_string($type) ? Str::limit(Str::slug($type), 80, '') : 'document-'.((int) $type + 1),
                        'disk' => 'private',
                        'path' => $path,
                        'original_name' => Str::limit(basename($file->getClientOriginalName()), 255, ''),
                        'mime_type' => (string) $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }

                $this->activities->record('application.submitted', $application);

                return $application;
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('private')->delete($path);
            }
            throw $exception;
        }

        SendApplicationConfirmation::dispatch($application->id)->afterCommit();

        return $application;
    }

    public function number(): string
    {
        return $this->generateNumber->handle();
    }
}
