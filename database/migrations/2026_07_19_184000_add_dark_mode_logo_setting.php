<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(['key' => 'logo_dark_url'], [
            'value' => '/images/logo-edsp-transparent.png',
            'type' => 'string',
            'group' => 'general',
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget('settings.public');
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'logo_dark_url')->delete();
        Cache::forget('settings.public');
    }
};
