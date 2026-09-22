<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex(['course_section_id', 'order']);
            $table->dropIndex(['course_id', 'order']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->renameColumn('course_section_id', 'section_id');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->index(['section_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex(['section_id', 'order']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->renameColumn('section_id', 'course_section_id');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->index(['course_section_id', 'order']);
            $table->index(['course_id', 'order']);
        });
    }
};
