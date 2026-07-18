<?php

namespace App\Actions;

use App\Models\Application;
use Illuminate\Support\Str;

final class GenerateApplicationNumber
{
    public function handle(): string
    {
        do {
            $number = 'EDSP-'.now()->format('Y').'-'.Str::upper(Str::random(8));
        } while (Application::query()->where('application_number', $number)->exists());

        return $number;
    }
}
