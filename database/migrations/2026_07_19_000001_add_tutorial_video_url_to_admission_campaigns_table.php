<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_campaigns', function (Blueprint $table): void {
            $table->string('tutorial_video_url', 2048)->nullable()->after('instructions');
        });
    }

    public function down(): void
    {
        Schema::table('admission_campaigns', function (Blueprint $table): void {
            $table->dropColumn('tutorial_video_url');
        });
    }
};
