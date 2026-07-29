<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['accreditation_reference_label', 'Habilitation de l’offre de formation', 'string'],
            ['accreditation_reference', 'Arrêté n°34682/2025-MESUPRES portant habilitation de l’offre de formation dispensée par l’établissement d’enseignement supérieur dénommé « Université de Mahajanga – École de Droit et Science Politique – EDSP »', 'text'],
            ['accreditation_reference_label_en', 'Degree programme accreditation', 'string'],
            ['accreditation_reference_en', 'Order No. 34682/2025-MESUPRES accrediting the degree programmes delivered by the higher education institution known as “University of Mahajanga – School of Law and Political Science (EDSP)”', 'text'],
        ];

        foreach ($settings as [$key, $value, $type]) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => $type,
                    'group' => 'legal',
                    'is_public' => true,
                ],
            );
        }
    }

    public function down(): void
    {
        Setting::query()
            ->whereIn('key', [
                'accreditation_reference_label',
                'accreditation_reference',
                'accreditation_reference_label_en',
                'accreditation_reference_en',
            ])
            ->delete();
    }
};
