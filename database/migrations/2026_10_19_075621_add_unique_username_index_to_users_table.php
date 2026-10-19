<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The index is on LOWER(username) so "Pyae" and "pyae" count as the same name.
     */
    public function up(): void
    {
        DB::statement('CREATE UNIQUE INDEX users_username_lower_unique ON users (LOWER(username))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_username_lower_unique');
    }
};
