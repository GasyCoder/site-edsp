<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_TITLE = 'Construisez votre avenir dans le droit et la science politique';

    private const NEW_TITLE = 'Comprendre le droit. Agir sur la société.';

    private const OLD_CONTENT = 'Une formation universitaire exigeante, ouverte sur les enjeux juridiques, institutionnels et politiques contemporains.';

    private const NEW_CONTENT = 'L’EDSP vous forme à l’analyse juridique, aux institutions et aux politiques publiques, de la Licence au Master, au cœur de Mahajanga.';

    public function up(): void
    {
        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->where('title', self::OLD_TITLE)
            ->update(['title' => self::NEW_TITLE]);

        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->where('content', self::OLD_CONTENT)
            ->update(['content' => self::NEW_CONTENT]);

        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->where('subtitle', 'École de Droit et Science Politique')
            ->update(['subtitle' => null]);

        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->where('button_text', 'Découvrir nos formations')
            ->update(['button_text' => 'Découvrir les parcours']);
    }

    public function down(): void
    {
        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->where('title', self::NEW_TITLE)
            ->update(['title' => self::OLD_TITLE]);

        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->where('content', self::NEW_CONTENT)
            ->update(['content' => self::OLD_CONTENT]);

        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->whereNull('subtitle')
            ->update(['subtitle' => 'École de Droit et Science Politique']);

        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->where('button_text', 'Découvrir les parcours')
            ->update(['button_text' => 'Découvrir nos formations']);
    }
};
