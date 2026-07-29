<?php

namespace App\Services;

use App\Jobs\OptimizeMediaImage;
use App\Models\Media;

final class MediaVariantDispatcher
{
    public function dispatch(Media|int $media): void
    {
        $mediaId = $media instanceof Media ? $media->getKey() : $media;

        if (config('media.optimize_after_response', true)) {
            OptimizeMediaImage::dispatchAfterResponse($mediaId);

            return;
        }

        OptimizeMediaImage::dispatch($mediaId)->afterCommit();
    }
}
