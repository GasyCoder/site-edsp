<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class ManageNewsletterSubscribers extends ManageRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getHeading(): string
    {
        return 'Abonnés à la newsletter';
    }

    public function getSubheading(): string
    {
        $activeCount = NewsletterSubscriber::query()->active()->count();
        $pendingCount = NewsletterSubscriber::query()
            ->whereNull('verified_at')
            ->whereNull('unsubscribed_at')
            ->count();

        return "{$activeCount} adresse(s) active(s) et éligible(s) aux envois · {$pendingCount} en attente de confirmation.";
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Ajouter un abonné')];
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordCreation(array $data): Model
    {
        return NewsletterSubscriber::query()->create([
            ...$data,
            'source' => 'admin',
            'verified_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }
}
