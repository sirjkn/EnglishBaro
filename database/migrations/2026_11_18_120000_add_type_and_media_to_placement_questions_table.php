<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('placement_questions', function (Blueprint $table) {
            $table->string('type')->default('single_choice')->after('question');
            $table->foreignId('media_id')->nullable()->after('type')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('placement_questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('media_id');
            $table->dropColumn('type');
        });
    }
};
