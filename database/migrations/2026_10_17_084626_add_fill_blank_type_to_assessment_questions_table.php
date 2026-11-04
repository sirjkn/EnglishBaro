<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite has no ALTER TABLE ... DROP/ADD CONSTRAINT, so the
            // CHECK(...) that Schema::enum() generated has to be rebuilt by
            // recreating the table (safe here: structure-only change).
            Schema::table('assessment_questions', function (Blueprint $table) {
                $table->string('type_new')->default('multiple_choice');
            });
            DB::statement('UPDATE assessment_questions SET type_new = type');
            Schema::table('assessment_questions', function (Blueprint $table) {
                $table->dropColumn('type');
            });
            Schema::table('assessment_questions', function (Blueprint $table) {
                $table->renameColumn('type_new', 'type');
            });

            return;
        }

        DB::statement('ALTER TABLE assessment_questions DROP CONSTRAINT assessment_questions_type_check');
        DB::statement("ALTER TABLE assessment_questions ADD CONSTRAINT assessment_questions_type_check CHECK (type IN ('multiple_choice', 'true_false', 'short_answer', 'fill_blank'))");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE assessment_questions DROP CONSTRAINT assessment_questions_type_check');
        DB::statement("ALTER TABLE assessment_questions ADD CONSTRAINT assessment_questions_type_check CHECK (type IN ('multiple_choice', 'true_false', 'short_answer'))");
    }
};
