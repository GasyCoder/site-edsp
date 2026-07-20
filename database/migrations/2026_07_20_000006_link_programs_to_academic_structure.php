<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->foreignId('mention_id')
                ->nullable()
                ->after('department_id')
                ->constrained('mentions')
                ->nullOnDelete();
        });

        Schema::create('program_parcours_level', function (Blueprint $table): void {
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parcours_level_id')->constrained('parcours_levels')->cascadeOnDelete();
            $table->primary(['program_id', 'parcours_level_id']);
        });

        $departments = DB::table('departments')->pluck('slug', 'id');
        $mentions = DB::table('mentions')->pluck('id', 'code');

        DB::table('programs')->orderBy('id')->each(function (object $program) use ($departments, $mentions): void {
            $identity = Str::lower(implode(' ', [
                $program->title,
                $program->mention,
                $program->domain,
                $departments[$program->department_id] ?? null,
            ]));
            $mentionCode = Str::contains($identity, ['polit', 'scpo']) ? 'SCPO' : 'DROIT';
            $mentionId = $mentions[$mentionCode] ?? null;

            if ($mentionId === null) {
                return;
            }

            DB::table('programs')->where('id', $program->id)->update(['mention_id' => $mentionId]);

            $parcoursLevelIds = DB::table('parcours_levels')
                ->join('parcours', 'parcours.id', '=', 'parcours_levels.parcours_id')
                ->where('parcours.mention_id', $mentionId)
                ->where('parcours_levels.is_active', true)
                ->pluck('parcours_levels.id');

            foreach ($parcoursLevelIds as $parcoursLevelId) {
                DB::table('program_parcours_level')->insertOrIgnore([
                    'program_id' => $program->id,
                    'parcours_level_id' => $parcoursLevelId,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_parcours_level');

        Schema::table('programs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('mention_id');
        });
    }
};
