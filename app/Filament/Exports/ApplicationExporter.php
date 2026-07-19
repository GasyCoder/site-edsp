<?php

namespace App\Filament\Exports;

use App\Models\Application;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ApplicationExporter extends Exporter
{
    protected static ?string $model = Application::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('application_number')
                ->label('Numéro de dossier')
                ->preventFormulaInjection(),
            ExportColumn::make('last_name')
                ->label('Nom')
                ->preventFormulaInjection(),
            ExportColumn::make('first_name')
                ->label('Prénom(s)')
                ->preventFormulaInjection(),
            ExportColumn::make('civility')->label('Civilité')->preventFormulaInjection(),
            ExportColumn::make('gender')->label('Genre')->preventFormulaInjection(),
            ExportColumn::make('email')
                ->label('Email')
                ->preventFormulaInjection(),
            ExportColumn::make('phone')
                ->label('Téléphone')
                ->preventFormulaInjection(),
            ExportColumn::make('campaign.title')
                ->label('Campagne')
                ->preventFormulaInjection(),
            ExportColumn::make('program.title')
                ->label('Formation')
                ->preventFormulaInjection(),
            ExportColumn::make('academicLevel.code')->label('Niveau')->preventFormulaInjection(),
            ExportColumn::make('mention.nom')->label('Mention')->preventFormulaInjection(),
            ExportColumn::make('parcours.nom')->label('Parcours')->preventFormulaInjection(),
            ExportColumn::make('father_name')->label('Nom du père')->preventFormulaInjection(),
            ExportColumn::make('mother_name')->label('Nom de la mère')->preventFormulaInjection(),
            ExportColumn::make('parent_phone')->label('Téléphone des parents')->preventFormulaInjection(),
            ExportColumn::make('guardian_name')->label('Répondant')->preventFormulaInjection(),
            ExportColumn::make('guardian_phone')->label('Téléphone du répondant')->preventFormulaInjection(),
            ExportColumn::make('last_diploma')->label('Dernier diplôme')->preventFormulaInjection(),
            ExportColumn::make('graduation_year')->label('Année d’obtention'),
            ExportColumn::make('previous_institution')->label('Établissement précédent')->preventFormulaInjection(),
            ExportColumn::make('status')
                ->label('Statut')
                ->preventFormulaInjection(),
            ExportColumn::make('submitted_at')
                ->label('Date de soumission'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = Number::format($export->successful_rows).' dossier(s) ont été exporté(s).';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' ligne(s) n’ont pas pu être exportée(s).';
        }

        return $body;
    }
}
