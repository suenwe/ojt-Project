
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_media', function (Blueprint $table) {
            $table->bigIncrements('media_id');
            $table->unsignedBigInteger('post_id');
            $table->string('media_type', 10); // photo or video
            $table->string('mime_type', 100);
            $table->string('original_filename')->nullable();
            $table->binary('media_data');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

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
