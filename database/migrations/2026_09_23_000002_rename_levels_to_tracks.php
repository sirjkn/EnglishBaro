<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('levels', 'tracks');

        Schema::table('tracks', function (Blueprint $table) {
            $table->renameColumn('code', 'track_code');
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->text('learning_outcomes')->nullable()->after('description');
            $table->foreignId('thumbnail_id')->nullable()->after('learning_outcomes')->constrained('media')->nullOnDelete();
            $table->decimal('price', 10, 2)->default(0)->after('thumbnail_id');
            $table->decimal('price_africa', 10, 2)->nullable()->after('price');
            $table->decimal('price_europe', 10, 2)->nullable()->after('price_africa');
            $table->decimal('price_north_america', 10, 2)->nullable()->after('price_europe');
            $table->decimal('price_asia', 10, 2)->nullable()->after('price_north_america');
            $table->string('currency', 3)->default('USD')->after('price_asia');
            // Site-wide subscription length: one payment unlocks a whole track for 120 days.
            $table->unsignedInteger('subscription_days')->default(120)->after('currency');
            $table->string('status', 20)->default('draft')->after('subscription_days');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->string('seo_title')->nullable()->after('is_featured');
            $table->string('seo_description')->nullable()->after('seo_title');
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->renameColumn('level_id', 'track_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->renameColumn('track_id', 'level_id');
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('thumbnail_id');
            $table->dropColumn([
                'learning_outcomes', 'price', 'price_africa', 'price_europe',
                'price_north_america', 'price_asia', 'currency', 'subscription_days',
                'status', 'is_featured', 'seo_title', 'seo_description',
            ]);
            $table->renameColumn('track_code', 'code');
        });

        Schema::rename('tracks', 'levels');
    }
};
