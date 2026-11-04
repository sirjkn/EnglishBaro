<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modules a staff account's access can be individually toggled for.
     * Admin accounts always have every module; this list only matters for
     * the 'staff' category.
     */
    public const MODULES = [
        'dashboard' => 'Dashboard',
        'students' => 'Students',
        'sessions' => 'Sessions',
        'testimonials' => 'Testimonials',
        'tracks' => 'Course Tracks',
        'levels' => 'Levels',
        'enrollments' => 'Enrollments',
        'placement-test' => 'Placement Test',
        'answer-reviews' => 'Answer Reviews',
        'payments' => 'Payments',
        'support' => 'Support',
        'audit-logs' => 'Audit Logs',
        'settings' => 'Settings',
        'users' => 'User Management',
    ];

    public function up(): void
    {
        // Postgres' enum() is a CHECK constraint, not a native type — widen
        // it to also allow 'staff' alongside the existing admin/student.
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type IN ('admin', 'staff', 'student'))");

        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['permission_id', 'user_id']);
        });

        foreach (self::MODULES as $slug => $name) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => "module.{$slug}"],
                ['name' => $name, 'description' => "Access the {$name} module", 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');

        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type IN ('admin', 'student'))");
    }
};
