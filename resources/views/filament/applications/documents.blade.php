<div class="space-y-3">
    @forelse ($documents as $document)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 dark:border-white/10">
            <div>
                <p class="font-medium">{{ $document->original_name }}</p>
                <p class="text-sm text-gray-500">{{ $document->type }} · {{ Number::fileSize((int) $document->size) }}</p>
            </div>

            @if ($canDownload)
                <a
                    href="{{ route('application-documents.download', $document) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-medium text-primary-600 hover:underline dark:text-primary-400"
                >
                    Télécharger
                </a>
            @else
                <span class="text-sm text-gray-500">Téléchargement non autorisé</span>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-500">Aucune pièce jointe à ce dossier.</p>
    @endforelse
</div>
