<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_answers', function (Blueprint $table) {
            // Short-answer questions can have multiple valid phrasings, so they
            // aren't graded purely by exact-match — an admin reviews them.
            // Null for multiple-choice/true-false answers, which stay auto-graded.
            $table->string('review_status', 20)->nullable()->after('is_correct');
            $table->text('review_remarks')->nullable()->after('review_status');
            $table->foreignId('reviewed_by')->nullable()->after('review_remarks')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['review_status', 'review_remarks', 'reviewed_at']);
        });
    }
};
