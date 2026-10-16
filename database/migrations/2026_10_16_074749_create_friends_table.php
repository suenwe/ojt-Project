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
        Schema::create('friends', function (Blueprint $table) {
            $table->unsignedBigInteger('first_user_id');
            $table->unsignedBigInteger('second_user_id');

            $table->enum('state', [
                'friends',
                'requested',
                'block',
            ])->default('requested');

            $table->primary(['first_user_id', 'second_user_id']);

            $table->foreign('first_user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('second_user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friends');
    }
};
