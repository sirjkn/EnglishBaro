<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['slug' => 'module.more-practice'],
            ['name' => 'More Practice', 'description' => 'Access the More Practice module', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('permissions')->where('slug', 'module.more-practice')->delete();
    }
};
