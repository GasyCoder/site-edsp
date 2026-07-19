<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('newsletter_campaigns')
            ->where('type', 'event')
            ->where('title', 'like', 'Annulation — %')
            ->update(['type' => 'event_cancellation']);
    }

    public function down(): void
    {
        DB::table('newsletter_campaigns')
            ->where('type', 'event_cancellation')
            ->update(['type' => 'event']);
    }
};
