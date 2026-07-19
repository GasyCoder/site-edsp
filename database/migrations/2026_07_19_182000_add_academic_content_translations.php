<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['levels', 'mentions', 'parcours'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->json('translations')->nullable();
            });
        }

        $this->translateByCode('levels', [
            'L1' => 'Bachelor 1',
            'L2' => 'Bachelor 2',
            'L3' => 'Bachelor 3',
            'M1' => 'Master 1',
            'M2' => 'Master 2',
        ]);

        $this->translateByCode('mentions', [
            'DROIT' => 'Law',
            'SP' => 'Political Science',
            'SCIENCE-POLITIQUE' => 'Political Science',
        ]);

        $this->translateByCode('parcours', [
            'DROIT-PRIVE' => 'Private Law',
            'DP' => 'Private Law',
            'SCIENCE-POLITIQUE' => 'Political Science',
            'SP' => 'Political Science',
        ]);
    }

    public function down(): void
    {
        foreach (['levels', 'mentions', 'parcours'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('translations');
            });
        }
    }

    /** @param array<string, string> $names */
    private function translateByCode(string $table, array $names): void
    {
        foreach ($names as $code => $name) {
            DB::table($table)
                ->whereRaw('UPPER(code) = ?', [mb_strtoupper($code)])
                ->update(['translations' => json_encode(['en' => ['nom' => $name]], JSON_UNESCAPED_UNICODE)]);
        }
    }
};
