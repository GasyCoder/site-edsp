<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        DB::table('pages')
            ->where('slug', 'accueil')
            ->update([
                'meta_title' => 'Accueil | EDSP - Ecole de Droit et Sciences Politique | Université de Mahajanga',
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        DB::table('pages')
            ->where('slug', 'accueil')
            ->where('meta_title', 'Accueil | EDSP - Ecole de Droit et Sciences Politique | Université de Mahajanga')
            ->update([
                'meta_title' => 'École de Droit et Science Politique | Université de Mahajanga',
            ]);
    }
};
