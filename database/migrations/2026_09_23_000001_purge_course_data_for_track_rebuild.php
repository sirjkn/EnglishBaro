<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The course -> track restructure changes nullable/NOT NULL foreign keys on
     * tables that already hold demo rows, so those rows are cleared first.
     */
    private const TABLES = [
        'lesson_progress',
        'course_progress',
        'assessment_answers',
        'assessment_attempts',
        'assessment_options',
        'assessment_questions',
        'assessments',
        'certificates',
        'subscriptions',
        'payment_callbacks',
        'payment_transactions',
        'payments',
        'enrollments',
        'lesson_resources',
        'resources',
        'lessons',
        'ebooks',
        'course_sections',
        'courses',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
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
