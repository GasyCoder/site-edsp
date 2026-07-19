<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->string('civility', 20)->nullable()->after('program_id');
            $table->string('gender', 20)->nullable()->after('last_name');
            $table->string('birth_place')->nullable()->after('birth_date');
            $table->string('nationality', 100)->nullable()->after('birth_place');
            $table->string('national_id', 80)->nullable()->after('nationality');

            $table->string('father_name')->nullable()->after('address');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('parent_phone', 40)->nullable()->after('mother_name');
            $table->string('guardian_name')->nullable()->after('parent_phone');
            $table->string('guardian_relationship', 100)->nullable()->after('guardian_name');
            $table->string('guardian_phone', 40)->nullable()->after('guardian_relationship');

            $table->foreignId('academic_level_id')->nullable()->after('guardian_phone')->constrained('levels')->nullOnDelete();
            $table->foreignId('mention_id')->nullable()->after('academic_level_id')->constrained('mentions')->nullOnDelete();
            $table->foreignId('parcours_id')->nullable()->after('mention_id')->constrained('parcours')->nullOnDelete();
            $table->string('last_diploma')->nullable()->after('parcours_id');
            $table->unsignedSmallInteger('graduation_year')->nullable()->after('last_diploma');
            $table->string('previous_institution')->nullable()->after('graduation_year');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('academic_level_id');
            $table->dropConstrainedForeignId('mention_id');
            $table->dropConstrainedForeignId('parcours_id');
            $table->dropColumn([
                'civility',
                'gender',
                'birth_place',
                'nationality',
                'national_id',
                'father_name',
                'mother_name',
                'parent_phone',
                'guardian_name',
                'guardian_relationship',
                'guardian_phone',
                'last_diploma',
                'graduation_year',
                'previous_institution',
            ]);
        });
    }
};
