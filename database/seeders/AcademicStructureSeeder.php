<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['mentions', 'parcours', 'levels', 'semestres'] as $table) {
            $this->importInsert($table.'.sql', $table);
        }

        $now = now();
        $links = [
            ['id' => 1, 'parcours_id' => 1, 'level_id' => 1],
            ['id' => 2, 'parcours_id' => 1, 'level_id' => 2],
            ['id' => 3, 'parcours_id' => 2, 'level_id' => 3],
            ['id' => 4, 'parcours_id' => 3, 'level_id' => 4],
            ['id' => 5, 'parcours_id' => 3, 'level_id' => 5],
            ['id' => 6, 'parcours_id' => 4, 'level_id' => 1],
            ['id' => 7, 'parcours_id' => 4, 'level_id' => 2],
            ['id' => 8, 'parcours_id' => 4, 'level_id' => 3],
            ['id' => 9, 'parcours_id' => 5, 'level_id' => 4],
            ['id' => 10, 'parcours_id' => 5, 'level_id' => 5],
        ];

        foreach ($links as $link) {
            DB::table('parcours_levels')->updateOrInsert(
                ['id' => $link['id']],
                [...$link, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            );
        }

        $this->importInsert('ues.sql', 'ues');
        $this->importInsert('ecs.sql', 'ecs');
        $this->importInsert('sessions_examens.sql', 'sessions_examens');
    }

    private function importInsert(string $file, string $table): void
    {
        $path = database_path('data/academic/'.$file);
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Impossible de lire le fichier académique {$file}.");
        }

        $pattern = '/INSERT INTO `'.preg_quote($table, '/').'`\s*\([^;]+?\) VALUES\s*.+?;/s';

        if (! preg_match($pattern, $contents, $matches)) {
            throw new RuntimeException("Aucune instruction INSERT trouvée pour {$table}.");
        }

        $statement = preg_replace('/^INSERT INTO/', 'INSERT IGNORE INTO', trim($matches[0]), 1);

        if (! is_string($statement)) {
            throw new RuntimeException("Impossible de préparer les données de {$table}.");
        }

        DB::unprepared($statement);
    }
}
