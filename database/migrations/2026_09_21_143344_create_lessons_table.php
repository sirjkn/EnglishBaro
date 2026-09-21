<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('video_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('ebook_id')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->timestamps();

            $table->index(['course_section_id', 'order']);
            $table->index(['course_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
