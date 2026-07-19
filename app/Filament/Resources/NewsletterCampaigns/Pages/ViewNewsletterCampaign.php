<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ViewNewsletterCampaign extends ViewRecord
{
    protected static string $resource = NewsletterCampaignResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getHeading(): string
    {
        return $this->getRecord()->title;
    }

    public function getSubheading(): string
    {
        return 'Aperçu du contenu et résultats de diffusion.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux campagnes')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(NewsletterCampaignResource::getUrl('index')),
            EditAction::make()
                ->label('Modifier')
                ->visible(fn (): bool => $this->getRecord()->canBePrepared())
                ->url(fn (): string => NewsletterCampaignResource::getUrl('edit', ['record' => $this->getRecord()])),
            NewsletterCampaignResource::sendAction(),
            NewsletterCampaignResource::scheduleAction(),
            NewsletterCampaignResource::pauseAction(),
            NewsletterCampaignResource::resumeAction(),
            NewsletterCampaignResource::reopenAction(),
            NewsletterCampaignResource::cancelAction(),
            NewsletterCampaignResource::announceEventCancellationAction(),
        ];
    }
}
