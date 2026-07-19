<?php

namespace App\Http\Controllers;

use App\Models\NewsletterCampaign;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterCampaignAttachmentController extends Controller
{
    use AuthorizesRequests;

    public function download(NewsletterCampaign $campaign): StreamedResponse
    {
        $this->authorize('view', $campaign);
        abort_unless(filled($campaign->attachment_path), 404);

        $disk = $campaign->attachment_disk ?: 'private';
        abort_unless(Storage::disk($disk)->exists($campaign->attachment_path), 404);

        return Storage::disk($disk)->download(
            $campaign->attachment_path,
            $campaign->attachment_name,
            ['Cache-Control' => 'private, no-store, max-age=0'],
        );
    }
}
