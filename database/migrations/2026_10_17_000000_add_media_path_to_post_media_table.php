<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('post_media')) {
            return;
        }

        Schema::table('post_media', function (Blueprint $table) {
            if (! Schema::hasColumn('post_media', 'media_path')) {
                $table->string('media_path')->nullable();
            }

            $table->binary('media_data')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('post_media', 'media_path')) {
            Schema::table('post_media', function (Blueprint $table) {
                $table->dropColumn('media_path');
            });
        }
    }
};
