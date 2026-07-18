<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivity extends TableWidget
{
    protected static ?string $heading = 'Activités récentes';

    public static function canView(): bool
    {
        return auth()->user()?->can('view activity logs') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ActivityLog::query()->with('user')->latest()->limit(10))
            ->columns([
                TextColumn::make('action')
                    ->label('Action')
                    ->badge(),
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->default('Système'),
                TextColumn::make('subject_type')
                    ->label('Objet')
                    ->formatStateUsing(fn (?string $state): string => class_basename($state ?? '—')),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->sortable(),
            ]);
    }
}
