<div class="space-y-4">
    @forelse ($documents as $document)
        @php
            $isImage = in_array($document->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true);
            $isPdf = $document->mime_type === 'application/pdf';
            $canPreview = $canDownload && ($isImage || $isPdf);
        @endphp

        <article class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-white/5">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-gray-950 dark:text-white" title="{{ $document->original_name }}">{{ $document->original_name }}</p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $document->type }} · {{ Number::fileSize((int) $document->size) }}</p>
                </div>

                @if ($canDownload)
                    <a
                        href="{{ route('application-documents.download', $document) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:border-primary-500 hover:text-primary-600 dark:border-white/15 dark:text-gray-200 dark:hover:border-primary-500 dark:hover:text-primary-400"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="size-4" />
                        Télécharger
                    </a>
                @endif
            </div>

            @if ($canPreview && $isImage)
                <div class="grid min-h-48 place-items-center bg-gray-50 p-4 dark:bg-black/15">
                    <img
                        src="{{ route('application-documents.preview', $document) }}"
                        alt="Aperçu de {{ $document->original_name }}"
                        class="max-h-80 w-auto max-w-full rounded-lg object-contain shadow-sm"
                        loading="lazy"
                    >
                </div>
            @elseif ($canPreview && $isPdf)
                <iframe
                    src="{{ route('application-documents.preview', $document) }}#toolbar=1&navpanes=0"
                    title="Aperçu PDF de {{ $document->original_name }}"
                    class="h-96 w-full bg-gray-100 dark:bg-gray-900"
                    loading="lazy"
                    referrerpolicy="no-referrer"
                ></iframe>
            @elseif ($canDownload)
                <div class="flex items-center gap-3 bg-gray-50 px-4 py-5 text-sm text-gray-600 dark:bg-black/15 dark:text-gray-300">
                    <span class="grid size-10 flex-none place-items-center rounded-lg bg-white text-gray-500 shadow-sm dark:bg-white/10 dark:text-gray-300">
                        <x-filament::icon icon="heroicon-o-document-text" class="size-5" />
                    </span>
                    <p>L’aperçu de ce format n’est pas pris en charge par le navigateur. Utilisez le bouton de téléchargement.</p>
                </div>
            @else
                <p class="px-4 py-5 text-sm text-gray-500">Aperçu non autorisé.</p>
            @endif
        </article>
    @empty
        <p class="text-sm text-gray-500">Aucune pièce jointe à ce dossier.</p>
    @endforelse
</div>
