<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        Schema::create($tableNames['permissions'], function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotPermission, $modelMorphKey): void {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($modelMorphKey);
            $table->index([$modelMorphKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            $table->primary([$pivotPermission, $modelMorphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $pivotRole, $modelMorphKey): void {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($modelMorphKey);
            $table->index([$modelMorphKey, 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            $table->primary([$pivotRole, $modelMorphKey, 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission): void {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        Schema::create('galleries', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('cover_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gallery_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['gallery_id', 'media_id']);
            $table->index(['gallery_id', 'is_visible', 'position']);
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable()->index();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->boolean('is_public')->default(true)->index();
            $table->string('status')->default('draft')->index();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documentables', function (Blueprint $table): void {
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->morphs('documentable');
            $table->primary(['document_id', 'documentable_id', 'documentable_type'], 'documentables_primary');
        });

        Schema::create('galleryables', function (Blueprint $table): void {
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $table->morphs('galleryable');
            $table->primary(['gallery_id', 'galleryable_id', 'galleryable_type'], 'galleryables_primary');
        });

        Schema::table('news', function (Blueprint $table): void {
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->string('canonical_url')->nullable()->after('meta_keywords');
            $table->boolean('robots_index')->default(true)->after('canonical_url');
            $table->boolean('robots_follow')->default(true)->after('robots_index');
            $table->string('og_title')->nullable()->after('robots_follow');
            $table->text('og_description')->nullable()->after('og_title');
            $table->foreignId('og_image_id')->nullable()->after('og_description')->constrained('media')->nullOnDelete();
        });

        Schema::table('programs', function (Blueprint $table): void {
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->string('canonical_url')->nullable()->after('meta_keywords');
            $table->boolean('robots_index')->default(true)->after('canonical_url');
            $table->boolean('robots_follow')->default(true)->after('robots_index');
            $table->string('og_title')->nullable()->after('robots_follow');
            $table->text('og_description')->nullable()->after('og_title');
            $table->foreignId('og_image_id')->nullable()->after('og_description')->constrained('media')->nullOnDelete();
        });

        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('handled_at')->index();
        });

        app('cache')->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->dropColumn('archived_at');
        });

        Schema::table('programs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('og_image_id');
            $table->dropColumn(['meta_keywords', 'canonical_url', 'robots_index', 'robots_follow', 'og_title', 'og_description']);
        });

        Schema::table('news', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('og_image_id');
            $table->dropColumn(['meta_keywords', 'canonical_url', 'robots_index', 'robots_follow', 'og_title', 'og_description']);
        });

        Schema::dropIfExists('galleryables');
        Schema::dropIfExists('documentables');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('galleries');

        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
