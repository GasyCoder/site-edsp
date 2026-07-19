<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class EditNewsletterCampaign extends EditRecord
{
    protected static string $resource = NewsletterCampaignResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    public function getHeading(): string
    {
        return 'Préparer : '.$this->getRecord()->title;
    }

    public function getSubheading(): string
    {
        return 'Enregistrez vos modifications avant d’envoyer ou de programmer la campagne.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux campagnes')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(NewsletterCampaignResource::getUrl('index')),
            ViewAction::make()
                ->label('Aperçu et résultats')
                ->url(fn (): string => NewsletterCampaignResource::getUrl('view', ['record' => $this->getRecord()])),
            NewsletterCampaignResource::sendAction(),
            NewsletterCampaignResource::scheduleAction(),
            NewsletterCampaignResource::pauseAction(),
            NewsletterCampaignResource::resumeAction(),
            NewsletterCampaignResource::reopenAction(),
            NewsletterCampaignResource::cancelAction(),
            NewsletterCampaignResource::announceEventCancellationAction(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Brouillon enregistré';
    }
}
