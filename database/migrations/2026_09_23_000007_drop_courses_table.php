<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('courses');
    }

    public function down(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('course_code', 20)->nullable()->unique();
            $table->text('description')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->foreignId('level_id')->nullable();
            $table->foreignId('thumbnail_id')->nullable()->constrained('media')->nullOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('price_africa', 10, 2)->nullable();
            $table->decimal('price_europe', 10, 2)->nullable();
            $table->decimal('price_north_america', 10, 2)->nullable();
            $table->decimal('price_asia', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->unsignedInteger('duration_days')->default(120);
            $table->unsignedInteger('subscription_days')->default(120);
            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
