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
            'title' => 'Inscriptions '.$year,
            'opens_at' => now()->startOfDay()->subDay(),
            'closes_at' => now()->addMonths(2)->endOfDay(),
            'instructions' => '<p>Complétez soigneusement le formulaire et vérifiez vos informations avant l’envoi. Les conditions officielles publiées par l’établissement prévalent.</p>',
            'required_documents' => ['Pièce d’identité', 'Diplôme ou attestation', 'Relevé de notes'],
            'status' => 'published', 'is_visible' => true,
        ]);
        $campaign->update(['translations' => ['en' => [
            'title' => 'Applications '.$year,
            'instructions' => '<p>Complete the form carefully and review your information before submitting it. Official requirements published by the School take precedence.</p>',
            'required_documents' => ['Identity document', 'Diploma or certificate', 'Academic transcript'],
        ]]]);
        $campaign->programs()->sync(Program::published()->pluck('id'));
    }
}
