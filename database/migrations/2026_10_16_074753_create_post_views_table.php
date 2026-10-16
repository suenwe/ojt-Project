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
        Schema::create('post_views', function (Blueprint $table) {
            $table->bigIncrements('view_id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('post_id')
                ->references('post_id')->on('posts')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->index(['post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_views');
    }
};
