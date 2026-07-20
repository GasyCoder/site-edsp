<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function preview(Request $request, Document $document)
    {
        $this->authorizePublicAccess($request, $document);
        abort_unless($document->mime_type === 'application/pdf', 404);

        return Storage::disk($document->disk)->response($document->path, $document->original_name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $document->original_name).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'self'; sandbox",
        ]);
    }

    public function download(Request $request, Document $document)
    {
        $this->authorizePublicAccess($request, $document);

        return Storage::disk($document->disk)->download($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }

    private function authorizePublicAccess(Request $request, Document $document): void
    {
        abort_unless($document->isPubliclyAvailable() || ($request->user()?->can('view documents') ?? false), 404);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);
    }
}
