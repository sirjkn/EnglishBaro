<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The plain UNIQUE(email) constraint blocks re-registering an email
     * that belongs to a soft-deleted user, since the row still physically
     * exists. A partial unique index (only enforced for non-deleted rows)
     * lets a deleted account's email be reused while still preventing two
     * active accounts from sharing one.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS users_email_unique');
        } else {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_unique');
        }

        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_email_unique');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email)');
    }
};
