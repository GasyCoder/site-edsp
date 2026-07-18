<?php

namespace App\Filament\Resources\AdmissionCampaigns\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\AdmissionCampaigns\AdmissionCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAdmissionCampaigns extends ManageRecords
{
    use TracksContentRevisions;

    protected static string $resource = AdmissionCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
