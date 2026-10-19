<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_media', function (Blueprint $table) {
            $table->bigIncrements('media_id');

            $table->unsignedBigInteger('post_id');

            $table->string('media_type', 20);
            $table->string('mime_type', 100);
            $table->string('original_filename')->nullable();

            // PostgreSQL BYTEA: stores the actual file bytes.
            $table->binary('media_data')->nullable();

            // No file path is needed for byte-based storage.
            $table->string('media_path')->nullable();

            $table->bigInteger('file_size')->nullable();

            $table->foreign('post_id')
                ->references('post_id')
                ->on('posts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};