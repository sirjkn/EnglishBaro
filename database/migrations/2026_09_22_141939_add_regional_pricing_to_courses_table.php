<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('price_africa', 10, 2)->nullable()->after('price');
            $table->decimal('price_europe', 10, 2)->nullable()->after('price_africa');
            $table->decimal('price_north_america', 10, 2)->nullable()->after('price_europe');
            $table->decimal('price_asia', 10, 2)->nullable()->after('price_north_america');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['price_africa', 'price_europe', 'price_north_america', 'price_asia']);
        });
    }
};
