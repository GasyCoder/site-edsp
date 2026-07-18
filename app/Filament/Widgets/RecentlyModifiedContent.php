<?php

namespace App\Filament\Widgets;

use App\Models\ContentRevision;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentlyModifiedContent extends TableWidget
{
    protected static ?string $heading = 'Contenus récemment modifiés';

    public static function canView(): bool
    {
        return auth()->user()?->can('view revisions') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ContentRevision::query()->with('user')->latest()->limit(8))
            ->columns([
                TextColumn::make('revisionable_type')
                    ->label('Contenu')
                    ->formatStateUsing(fn (?string $state): string => class_basename($state ?? '—')),
                TextColumn::make('revisionable_id')
                    ->label('ID')
                    ->numeric(),
                TextColumn::make('action')
                    ->label('Action')
                    ->badge(),
                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->default('Système'),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->sortable(),
            ]);
    }
}
