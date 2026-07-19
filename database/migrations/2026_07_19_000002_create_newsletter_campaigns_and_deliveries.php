<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('email');
            $table->string('source', 50)->default('website')->after('name')->index();
            $table->timestamp('imported_at')->nullable()->after('unsubscribed_at');
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 30)->default('message')->index();
            $table->string('title');
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('content');
            $table->foreignId('news_id')->nullable()->constrained('news')->nullOnDelete();
            $table->timestamp('event_starts_at')->nullable();
            $table->string('event_location')->nullable();
            $table->string('external_url', 2048)->nullable();
            $table->string('external_url_label')->nullable();
            $table->string('attachment_disk', 30)->nullable();
            $table->string('attachment_path', 2048)->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->text('last_error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('newsletter_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('queued')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->unique(['newsletter_campaign_id', 'newsletter_subscriber_id'], 'newsletter_delivery_recipient_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_deliveries');
        Schema::dropIfExists('newsletter_campaigns');

        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->dropIndex(['source']);
            $table->dropColumn(['name', 'source', 'imported_at']);
        });
    }
};
