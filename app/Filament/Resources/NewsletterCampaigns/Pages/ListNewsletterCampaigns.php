<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use App\Models\NewsletterSubscriber;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListNewsletterCampaigns extends ListRecords
{
    protected static string $resource = NewsletterCampaignResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getHeading(): string
    {
        return 'Campagnes newsletter';
    }

    public function getSubheading(): string
    {
        return 'Rédigez et diffusez des actualités, événements ou messages à '.NewsletterSubscriber::query()->active()->count().' abonné(s) actif(s).';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Créer une campagne')];
    }
}
