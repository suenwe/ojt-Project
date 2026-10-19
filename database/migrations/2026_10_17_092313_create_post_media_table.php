<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The post_media table is normally created by 2026_10_16_080257; this only creates it for databases that skipped that one.
     */
    public function up(): void
    {
        if (Schema::hasTable('post_media')) {
            if (! Schema::hasColumn('post_media', 'media_path')) {
                Schema::table('post_media', function (Blueprint $table) {
                    $table->string('media_path')->nullable();
                });
            }

            return;
        }

        Schema::create('post_media', function (Blueprint $table) {
            $table->bigIncrements('media_id');

            $table->unsignedBigInteger('post_id');

            $table->string('media_type', 20);
            $table->string('mime_type', 100);
            $table->string('original_filename')->nullable();

            // PostgreSQL BYTEA: stores the actual file bytes.
            $table->binary('media_data')->nullable();

            $table->string('media_path')->nullable();

            $table->bigInteger('file_size')->nullable();
            $table->timestamps();

            $table->foreign('post_id')
                ->references('post_id')
                ->on('posts')
                ->cascadeOnDelete();
        });
    }

    /**
     * The table belongs to 2026_10_16_080257, which drops it.
     */
    public function down(): void
    {
        //
    }
};
