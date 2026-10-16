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
        Schema::create('reactions', function (Blueprint $table) {
            $table->bigIncrements('reaction_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('post_id');
            $table->string('type');

            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('post_id')
                ->references('post_id')->on('posts')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
