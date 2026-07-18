<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->string('disk')->default('public');
            $t->string('path');
            $t->string('filename');
            $t->string('original_name');
            $t->string('mime_type');
            $t->string('extension', 12);
            $t->unsignedBigInteger('size');
            $t->unsignedInteger('width')->nullable();
            $t->unsignedInteger('height')->nullable();
            $t->string('alt_text')->nullable();
            $t->text('caption')->nullable();
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('status')->index();
            $t->string('template')->default('default');
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->string('meta_keywords')->nullable();
            $t->string('canonical_url')->nullable();
            $t->boolean('robots_index')->default(true);
            $t->boolean('robots_follow')->default(true);
            $t->string('og_title')->nullable();
            $t->text('og_description')->nullable();
            $t->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('published_at')->nullable()->index();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('page_sections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('page_id')->constrained()->cascadeOnDelete();
            $t->string('section_key');
            $t->string('section_type');
            $t->string('title')->nullable();
            $t->string('subtitle')->nullable();
            $t->text('content')->nullable();
            $t->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('button_text')->nullable();
            $t->string('button_url')->nullable();
            $t->json('settings')->nullable();
            $t->unsignedSmallInteger('position')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['page_id', 'section_key']);
        });
        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('news_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('news', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('excerpt');
            $t->longText('content');
            $t->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $t->foreignId('category_id')->nullable()->constrained('news_categories')->nullOnDelete();
            $t->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status')->index();
            $t->boolean('is_featured')->default(false);
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->timestamp('published_at')->nullable()->index();
            $t->unsignedBigInteger('views')->default(0);
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('programs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('level');
            $t->string('domain')->nullable();
            $t->string('mention')->nullable();
            $t->string('track')->nullable();
            $t->text('description');
            $t->longText('objectives')->nullable();
            $t->longText('admission_requirements')->nullable();
            $t->longText('skills')->nullable();
            $t->longText('careers')->nullable();
            $t->string('duration')->nullable();
            $t->longText('curriculum')->nullable();
            $t->string('manager')->nullable();
            $t->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('status')->index();
            $t->unsignedSmallInteger('position')->default(0);
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('team_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->string('first_name');
            $t->string('last_name');
            $t->string('position');
            $t->text('biography')->nullable();
            $t->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->json('social_links')->nullable();
            $t->unsignedSmallInteger('display_order')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->string('status')->default('draft');
            $t->timestamps();
        });
        Schema::create('partners', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('url')->nullable();
            $t->foreignId('logo_id')->nullable()->constrained('media')->nullOnDelete();
            $t->unsignedSmallInteger('position')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
        });
        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->string('author_name');
            $t->string('author_role')->nullable();
            $t->text('content');
            $t->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
        });
        Schema::create('admission_campaigns', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('academic_year');
            $t->timestamp('opens_at');
            $t->timestamp('closes_at');
            $t->longText('instructions')->nullable();
            $t->json('required_documents')->nullable();
            $t->string('status')->index();
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
        });
        Schema::create('admission_campaign_program', function (Blueprint $t) {
            $t->foreignId('admission_campaign_id')->constrained()->cascadeOnDelete();
            $t->foreignId('program_id')->constrained()->cascadeOnDelete();
            $t->primary(['admission_campaign_id', 'program_id']);
        });
        Schema::create('applications', function (Blueprint $t) {
            $t->id();
            $t->uuid('public_id')->unique();
            $t->string('application_number')->unique();
            $t->foreignId('admission_campaign_id')->constrained();
            $t->foreignId('program_id')->constrained();
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email')->index();
            $t->string('phone');
            $t->date('birth_date');
            $t->string('address');
            $t->text('academic_background');
            $t->string('status')->default('submitted')->index();
            $t->text('internal_notes')->nullable();
            $t->boolean('privacy_accepted');
            $t->timestamp('submitted_at');
            $t->timestamps();
        });
        Schema::create('application_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('application_id')->constrained()->cascadeOnDelete();
            $t->string('type');
            $t->string('disk')->default('private');
            $t->string('path');
            $t->string('original_name');
            $t->string('mime_type');
            $t->unsignedBigInteger('size');
            $t->timestamps();
        });
        Schema::create('application_status_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('application_id')->constrained()->cascadeOnDelete();
            $t->string('old_status')->nullable();
            $t->string('new_status');
            $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('comment')->nullable();
            $t->timestamps();
        });
        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name');
            $t->string('email')->index();
            $t->string('phone')->nullable();
            $t->string('organization')->nullable();
            $t->string('subject');
            $t->text('message');
            $t->boolean('consent');
            $t->timestamp('read_at')->nullable();
            $t->timestamp('handled_at')->nullable();
            $t->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->string('type')->default('string');
            $t->string('group')->default('general');
            $t->boolean('is_public')->default(true);
            $t->timestamps();
        });
        Schema::create('content_revisions', function (Blueprint $t) {
            $t->id();
            $t->morphs('revisionable');
            $t->json('old_values')->nullable();
            $t->json('new_values');
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->nullableMorphs('subject');
            $t->string('action');
            $t->json('metadata')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->timestamps();
        });
        Schema::create('redirects', function (Blueprint $t) {
            $t->id();
            $t->string('source_path')->unique();
            $t->string('destination_url');
            $t->unsignedSmallInteger('status_code')->default(301);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['redirects', 'activity_logs', 'content_revisions', 'settings', 'contact_messages', 'application_status_histories', 'application_documents', 'applications', 'admission_campaign_program', 'admission_campaigns', 'testimonials', 'partners', 'team_members', 'programs', 'news', 'news_categories', 'departments', 'page_sections', 'pages', 'media'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
