<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('posts', 'post_type')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->string('post_type', 30)->default('Discussion');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'post_type')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('post_type');
            });
        }
    }
};
