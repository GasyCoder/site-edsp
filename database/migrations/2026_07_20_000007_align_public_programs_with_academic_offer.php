<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcours_levels', function (Blueprint $table): void {
            $table->boolean('is_common_core')->default(false)->after('is_active')->index();
        });

        DB::table('parcours_levels')
            ->join('parcours', 'parcours.id', '=', 'parcours_levels.parcours_id')
            ->join('levels', 'levels.id', '=', 'parcours_levels.level_id')
            ->where('parcours.code', 'DROI')
            ->where('levels.code', 'L1')
            ->update(['parcours_levels.is_common_core' => true]);

        $this->updateProgram('droit-prive', [
            'title' => 'Droit',
            'mention' => 'Droit',
            'domain' => 'Droit',
            'description' => 'Une formation juridique progressive, du tronc commun de Licence aux spécialisations en droit privé et droit des affaires.',
            'meta_title' => 'Mention Droit — Parcours de Licence et Master | EDSP',
            'meta_description' => 'Découvrez la mention Droit de l’EDSP : tronc commun, Droit privé et Droit des affaires, de la L1 au M2.',
        ], [
            'title' => 'Law',
            'mention' => 'Law',
            'domain' => 'Law',
            'description' => 'A progressive legal education from the undergraduate common core to specialisations in Private Law and Business Law.',
            'meta_title' => 'Law degree pathways — Bachelor to Master | EDSP',
            'meta_description' => 'Explore EDSP Law pathways from the common core through Private Law and Business Law, from L1 to M2.',
        ]);

        $this->updateProgram('science-politique', [
            'title' => 'Sciences Politiques',
            'mention' => 'Sciences Politiques',
            'domain' => 'Sciences Politiques',
            'description' => 'Une formation en sciences politiques allant des fondements de Licence à la spécialisation en études politiques au niveau Master.',
            'meta_title' => 'Mention Sciences Politiques — Licence et Master | EDSP',
            'meta_description' => 'Découvrez les parcours Science Politique et Études Politiques proposés par l’EDSP de la L1 au M2.',
        ], [
            'title' => 'Political Science',
            'mention' => 'Political Science',
            'domain' => 'Political Science',
            'description' => 'A Political Science education spanning undergraduate foundations and advanced Political Studies at Master level.',
            'meta_title' => 'Political Science pathways — Bachelor to Master | EDSP',
            'meta_description' => 'Explore EDSP Political Science and Political Studies pathways from L1 to M2.',
        ]);

        $this->updateAcademicTranslation('mentions', 'DROIT', [
            'nom' => 'Law',
            'description' => 'The Law subject area provides progressive legal education from L1 to M2.',
        ]);
        $this->updateAcademicTranslation('mentions', 'SCPO', [
            'nom' => 'Political Science',
            'description' => 'The Political Science subject area develops knowledge of institutions, public action and political analysis from L1 to M2.',
        ]);

        foreach ([
            'DROI' => ['nom' => 'Law', 'description' => 'Law pathway for L1 and L2, beginning with a common core in L1.'],
            'DPRI' => ['nom' => 'Private Law', 'description' => 'Private Law pathway for L3.'],
            'DAFF' => ['nom' => 'Business Law', 'description' => 'Business Law pathway for M1 and M2.'],
            'SCPO' => ['nom' => 'Political Science', 'description' => 'Political Science pathway for L1, L2 and L3.'],
            'ETPO' => ['nom' => 'Political Studies', 'description' => 'Political Studies pathway for M1 and M2.'],
        ] as $code => $translation) {
            $this->updateAcademicTranslation('parcours', $code, $translation);
        }
    }

    public function down(): void
    {
        Schema::table('parcours_levels', function (Blueprint $table): void {
            $table->dropColumn('is_common_core');
        });
    }

    /** @param array<string, string> $fields @param array<string, string> $englishFields */
    private function updateProgram(string $slug, array $fields, array $englishFields): void
    {
        $program = DB::table('programs')->where('slug', $slug)->first();

        if ($program === null) {
            return;
        }

        $translations = json_decode((string) ($program->translations ?? ''), true);
        $translations = is_array($translations) ? $translations : [];
        $translations['en'] = [...($translations['en'] ?? []), ...$englishFields];

        DB::table('programs')->where('id', $program->id)->update([
            ...$fields,
            'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);
    }

    /** @param array<string, string> $englishFields */
    private function updateAcademicTranslation(string $table, string $code, array $englishFields): void
    {
        $record = DB::table($table)->where('code', $code)->first();

        if ($record === null) {
            return;
        }

        $translations = json_decode((string) ($record->translations ?? ''), true);
        $translations = is_array($translations) ? $translations : [];
        $translations['en'] = [...($translations['en'] ?? []), ...$englishFields];

        DB::table($table)->where('id', $record->id)->update([
            'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);
    }
};
