<?php

namespace App\Filament\Exports;

use App\Models\NewsletterSubscriber;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class NewsletterSubscriberExporter extends Exporter
{
    protected static ?string $model = NewsletterSubscriber::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('email')->label('Adresse e-mail')->preventFormulaInjection(),
            ExportColumn::make('name')->label('Nom')->preventFormulaInjection(),
            ExportColumn::make('subscription_status')->label('Statut')->preventFormulaInjection(),
            ExportColumn::make('source')->label('Origine')->preventFormulaInjection(),
            ExportColumn::make('verified_at')->label('Confirmé le'),
            ExportColumn::make('unsubscribed_at')->label('Désinscrit le'),
            ExportColumn::make('created_at')->label('Ajouté le'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = Number::format($export->successful_rows).' abonné(s) exporté(s).';

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failed).' ligne(s) en échec.';
        }

        return $body;
    }
}
