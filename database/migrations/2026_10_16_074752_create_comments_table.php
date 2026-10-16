<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('comment_id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('parent_id')->nullable();

            $table->text('content');

            $table->timestamps();

            $table->foreign('post_id')
                ->references('post_id')->on('posts')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('parent_id')
                ->references('comment_id')->on('comments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
