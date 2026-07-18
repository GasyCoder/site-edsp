<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationDocumentController extends Controller
{
    public function download(Request $request, ApplicationDocument $document, ActivityLogger $activities)
    {
        abort_unless($request->user()->can('view', $document), 404);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);
        $activities->record('application.document_downloaded', $document->application, $request->user()->id, ['document_id' => $document->id]);

        return Storage::disk($document->disk)->download($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
