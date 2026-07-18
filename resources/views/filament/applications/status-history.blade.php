<ol class="space-y-3">
    @forelse ($history as $entry)
        <li class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">
                    {{ $entry->old_status?->label() ?? 'Création' }}
                    →
                    {{ $entry->new_status->label() }}
                </p>
                <time class="text-sm text-gray-500" datetime="{{ $entry->created_at?->toAtomString() }}">
                    {{ $entry->created_at?->format('d/m/Y H:i') }}
                </time>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ $entry->changedBy?->name ?? 'Système' }}
                @if ($entry->comment)
                    — {{ $entry->comment }}
                @endif
            </p>
        </li>
    @empty
        <li class="text-sm text-gray-500">Aucun changement de statut enregistré.</li>
    @endforelse
</ol>
