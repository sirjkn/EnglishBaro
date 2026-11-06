<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pricing and catalog-level fields now live on courses — a track is just
     * a unit listed inside one, unlocked as part of the course's payment
     * rather than sold on its own.
     */
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn([
                'price', 'price_africa', 'price_europe', 'price_north_america', 'price_asia',
                'currency', 'subscription_days', 'is_featured', 'seo_title', 'seo_description',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('thumbnail_id');
            $table->decimal('price_africa', 10, 2)->nullable()->after('price');
            $table->decimal('price_europe', 10, 2)->nullable()->after('price_africa');
            $table->decimal('price_north_america', 10, 2)->nullable()->after('price_europe');
            $table->decimal('price_asia', 10, 2)->nullable()->after('price_north_america');
            $table->string('currency', 3)->default('USD')->after('price_asia');
            $table->unsignedInteger('subscription_days')->default(120)->after('currency');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->string('seo_title')->nullable()->after('is_featured');
            $table->string('seo_description')->nullable()->after('seo_title');
        });
    }
};
