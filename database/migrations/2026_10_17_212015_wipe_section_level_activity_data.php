<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Activity Questions moves from one quiz per Section to one quiz per
     * Lesson. The admin/student flows and demo data are both section-scoped
     * today, so rather than try to remap them, the existing activity data is
     * discarded and admins re-enter it per lesson.
     */
    public function up(): void
    {
        foreach (['assessment_answers', 'assessment_options', 'assessment_questions', 'assessment_attempts', 'assessments'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }

    public function down(): void
    {
        //
    }
};
