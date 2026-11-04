<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('mux_asset_id')->nullable()->after('external_id');
            $table->string('mux_playback_id')->nullable()->after('mux_asset_id');
            $table->boolean('mux_ready')->default(false)->after('mux_playback_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE media DROP CONSTRAINT IF EXISTS media_provider_check');
            DB::statement("ALTER TABLE media ADD CONSTRAINT media_provider_check CHECK (provider IN ('local','youtube','imgur','vimeo','other','mux'))");
        }
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['mux_asset_id', 'mux_playback_id', 'mux_ready']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE media DROP CONSTRAINT IF EXISTS media_provider_check');
            DB::statement("ALTER TABLE media ADD CONSTRAINT media_provider_check CHECK (provider IN ('local','youtube','imgur','vimeo','other'))");
        }
    }
};
