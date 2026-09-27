<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            // A section-level activity, shown after all of a section's lessons
            // are complete. lesson_id stays for any future per-lesson quiz use.
            $table->foreignId('section_id')->nullable()->after('lesson_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
        });
    }
};
