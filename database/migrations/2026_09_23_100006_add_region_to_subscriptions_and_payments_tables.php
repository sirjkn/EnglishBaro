<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The pricing region a subscription/payment was made under. A student
        // who paid while resolved to one region must keep accessing lessons
        // from that same region — moving regions requires purchasing again.
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('region', 40)->nullable()->after('status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('region', 40)->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', fn (Blueprint $table) => $table->dropColumn('region'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('region'));
    }
};
