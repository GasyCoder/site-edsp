<?php

namespace App\Filament\Resources\DirectorMessages\Pages;

use App\Filament\Resources\DirectorMessages\DirectorMessageResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListDirectorMessages extends ListRecords
{
    protected static string $resource = DirectorMessageResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getHeading(): string
    {
        return 'Mot du directeur';
    }

    public function getSubheading(): string
    {
        return 'Modifiez le portrait, l’identité et le message affichés sur l’accueil et la page Présentation.';
    }
}
