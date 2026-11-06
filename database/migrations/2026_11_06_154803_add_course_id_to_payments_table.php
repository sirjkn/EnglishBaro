<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A payment now buys a course (which unlocks every track/unit under it),
     * not a single track — track_id is kept as the entry unit for receipts,
     * but course_id is the authoritative record of what was purchased.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('track_id')->constrained('courses')->restrictOnDelete();
        });

        DB::statement('
            UPDATE payments
            SET course_id = (SELECT course_id FROM tracks WHERE tracks.id = payments.track_id)
            WHERE track_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
