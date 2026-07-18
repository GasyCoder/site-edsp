<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $pageId = DB::table('pages')->where('slug', 'accueil')->value('id');

            if (! $pageId || DB::table('page_sections')->where('page_id', $pageId)->where('section_key', 'stats')->exists()) {
                return;
            }

            DB::table('page_sections')
                ->where('page_id', $pageId)
                ->where('position', '>=', 4)
                ->increment('position');

            DB::table('page_sections')->insert([
                'page_id' => $pageId,
                'section_key' => 'stats',
                'section_type' => 'stats',
                'title' => null,
                'subtitle' => null,
                'content' => null,
                'settings' => json_encode([
                    'background' => 'blue',
                    'alignment' => 'center',
                    'container' => 'wide',
                ], JSON_THROW_ON_ERROR),
                'position' => 4,
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $pageId = DB::table('pages')->where('slug', 'accueil')->value('id');

            if (! $pageId) {
                return;
            }

            $deleted = DB::table('page_sections')
                ->where('page_id', $pageId)
                ->where('section_key', 'stats')
                ->delete();

            if ($deleted) {
                DB::table('page_sections')
                    ->where('page_id', $pageId)
                    ->where('position', '>', 4)
                    ->decrement('position');
            }
        });
    }
};
