<?php

namespace Database\Seeders;

use App\Models\AdmissionCampaign;
use App\Models\Program;
use Illuminate\Database\Seeder;

class AdmissionsSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year.'-'.(now()->year + 1);
        $campaign = AdmissionCampaign::query()->updateOrCreate(['academic_year' => $year], [
            'title' => 'Préinscriptions '.$year,
            'opens_at' => now()->startOfDay()->subDay(),
            'closes_at' => now()->addMonths(2)->endOfDay(),
            'instructions' => '<p>Complétez soigneusement le formulaire et vérifiez vos informations avant l’envoi. Les conditions officielles publiées par l’établissement prévalent.</p>',
            'required_documents' => ['Pièce d’identité', 'Diplôme ou attestation', 'Relevé de notes'],
            'status' => 'published', 'is_visible' => true,
        ]);
        $campaign->programs()->sync(Program::published()->pluck('id'));
    }
}
