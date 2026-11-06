<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A course (e.g. "English", "Spanish") is now the thing a student
     * actually pays for. Tracks (A1, A2, B1, B2, ...) become units listed
     * inside a course instead of each being independently priced and sold —
     * see the next migration, which moves pricing off tracks onto courses.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code', 10)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->foreignId('thumbnail_id')->nullable()->constrained('media')->nullOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('price_africa', 10, 2)->nullable();
            $table->decimal('price_europe', 10, 2)->nullable();
            $table->decimal('price_north_america', 10, 2)->nullable();
            $table->decimal('price_asia', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            // One payment unlocks every unit (track) in the course for this many days.
            $table->unsignedInteger('subscription_days')->default(120);
            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
