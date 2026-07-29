<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image variants
    |--------------------------------------------------------------------------
    |
    | By default variants are generated after the HTTP response, without
    | requiring a queue worker. Set this to false on infrastructure where a
    | supervised queue worker is guaranteed to be running.
    |
    */
    'optimize_after_response' => env('MEDIA_OPTIMIZE_AFTER_RESPONSE', true),

    'optimized_width' => (int) env('MEDIA_OPTIMIZED_WIDTH', 1920),
    'optimized_quality' => (int) env('MEDIA_OPTIMIZED_QUALITY', 82),
    'thumbnail_width' => (int) env('MEDIA_THUMBNAIL_WIDTH', 640),
    'thumbnail_quality' => (int) env('MEDIA_THUMBNAIL_QUALITY', 78),
];
