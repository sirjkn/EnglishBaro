<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            // A long-lived cookie value that persists across logins/logouts,
            // so admins can tell "this is the same device as before" even
            // though a real MAC address is never visible over HTTP.
            $table->string('device_token', 64)->nullable()->after('user_agent');
            // Human-readable "City, Country" resolved from the login IP.
            $table->string('location', 120)->nullable()->after('ip_address');
            $table->index('device_token');
        });
    }

    public function down(): void
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropIndex(['device_token']);
            $table->dropColumn(['device_token', 'location']);
        });
    }
};
