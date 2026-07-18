<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentions', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('parcours', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mention_id')->constrained('mentions')->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('levels', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->unsignedSmallInteger('ordre')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('semestres', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->unsignedSmallInteger('ordre')->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('parcours_levels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parcours_id')->constrained('parcours')->restrictOnDelete();
            $table->foreignId('level_id')->constrained('levels')->restrictOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['parcours_id', 'level_id']);
        });

        Schema::create('ues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parcours_level_id')->constrained('parcours_levels')->restrictOnDelete();
            $table->foreignId('semestre_id')->constrained('semestres')->restrictOnDelete();
            $table->string('code');
            $table->string('nom');
            $table->unsignedSmallInteger('credits')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['parcours_level_id', 'semestre_id', 'code'], 'ues_parcours_semestre_code_unique');
        });

        Schema::create('ecs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ue_id')->constrained('ues')->restrictOnDelete();
            $table->string('code');
            $table->string('nom');
            $table->decimal('coefficient', 5, 2)->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_historical_marker')->default(false);
            $table->foreignId('replaced_by_ec_id')->nullable()->constrained('ecs')->nullOnDelete();
            $table->text('historical_comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['ue_id', 'code']);
            $table->index(['is_historical_marker', 'replaced_by_ec_id']);
        });

        Schema::create('sessions_examens', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->boolean('is_rattrapage')->default(false);
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions_examens');
        Schema::dropIfExists('ecs');
        Schema::dropIfExists('ues');
        Schema::dropIfExists('parcours_levels');
        Schema::dropIfExists('semestres');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('parcours');
        Schema::dropIfExists('mentions');
    }
};
