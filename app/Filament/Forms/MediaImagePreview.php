<?php

namespace App\Filament\Forms;

use App\Models\Media;
use Illuminate\Support\HtmlString;

final class MediaImagePreview
{
    public static function optionLabel(Media $media): string
    {
        $name = e($media->original_name);
        $thumbnail = $media->thumbnail_url;

        if (blank($thumbnail)) {
            return $name;
        }

        return '<span style="display:flex;align-items:center;gap:.75rem">'
            .'<img src="'.e($thumbnail).'" alt="" style="width:2.75rem;height:2.75rem;border-radius:.5rem;object-fit:cover;flex:none" />'
            .'<span style="min-width:0;overflow:hidden;text-overflow:ellipsis">'.$name.'</span>'
            .'</span>';
    }

    public static function render(?int $mediaId, string $emptyMessage = 'Aucune image sélectionnée.'): HtmlString
    {
        $media = $mediaId ? Media::query()->find($mediaId) : null;
        $imageUrl = $media?->image_url;

        if ($media === null || blank($imageUrl)) {
            return new HtmlString(
                '<div style="display:grid;min-height:12rem;place-items:center;border:1px dashed rgb(100 116 139 / .45);border-radius:.75rem;padding:1rem;color:rgb(148 163 184);background:rgb(15 23 42 / .025);font-size:.875rem;text-align:center">'
                .e($emptyMessage)
                .'</div>',
            );
        }

        $dimensions = $media->width && $media->height ? e($media->width.' × '.$media->height.' px') : 'Dimensions inconnues';
        $alternativeText = filled($media->alt_text) ? e($media->alt_text) : 'Sans texte alternatif';

        return new HtmlString(
            '<figure style="overflow:hidden;border:1px solid rgb(100 116 139 / .35);border-radius:.75rem;background:rgb(15 23 42 / .08)">'
            .'<div style="display:grid;height:14rem;place-items:center;padding:.75rem;background:rgb(248 250 252)">'
            .'<img src="'.e($imageUrl).'" alt="'.e($media->alt_text ?: $media->original_name).'" style="display:block;max-width:100%;width:100%;height:100%;object-fit:contain" />'
            .'</div>'
            .'<figcaption style="display:grid;gap:.25rem;padding:.75rem .9rem;font-size:.75rem;color:rgb(100 116 139);background:rgb(255 255 255 / .96)">'
            .'<strong style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:rgb(30 41 59)">'.e($media->original_name).'</strong>'
            .'<span>'.$dimensions.' · '.$alternativeText.'</span>'
            .'</figcaption>'
            .'</figure>',
        );
    }
}
