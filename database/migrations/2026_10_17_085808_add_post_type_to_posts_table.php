<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The post_type column is normally added by 2026_10_17_000001; this only adds it for databases that skipped that one.
     */
    public function up(): void
    {
        if (Schema::hasColumn('posts', 'post_type')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->string('post_type', 30)->default('Discussion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * The column belongs to 2026_10_17_000001, which removes it.
     */
    public function down(): void
    {
        //
    }
};
