<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table): void {
            $table->timestamp('paused_at')->nullable()->after('scheduled_at');
            $table->timestamp('cancelled_at')->nullable()->after('sent_at');
            $table->text('status_reason')->nullable()->after('last_error');
            $table->foreignId('status_changed_by')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('status_changed_by');
            $table->dropColumn(['paused_at', 'cancelled_at', 'status_reason']);
        });
    }
};
