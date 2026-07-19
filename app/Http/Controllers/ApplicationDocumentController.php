<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationDocumentController extends Controller
{
    /** @var list<string> */
    private const PREVIEWABLE_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function preview(Request $request, ApplicationDocument $document)
    {
        abort_unless($request->user()->can('view', $document), 404);
        abort_unless(in_array($document->mime_type, self::PREVIEWABLE_MIME_TYPES, true), 415);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->response(
            $document->path,
            $document->original_name,
            [
                'Content-Type' => $document->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Cache-Control' => 'private, no-store',
            ],
            'inline',
        );
    }

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
