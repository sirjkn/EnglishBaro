<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'order']);
        });

        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });

        Schema::rename('course_sections', 'sections');

        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            // Every level always has exactly the four fixed skill sections.
            $table->string('type', 20);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->unique(['level_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique(['level_id', 'type']);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('level_id');
            $table->dropColumn('type');
        });

        Schema::rename('sections', 'course_sections');

        Schema::table('course_sections', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->index(['course_id', 'order']);
        });
    }
};
