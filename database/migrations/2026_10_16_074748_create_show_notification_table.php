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
        Schema::create('show_notification', function (Blueprint $table) {
            $table->bigIncrements('sh_not_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('notification_id');
            $table->string('notification_type');

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('notification_id')
                ->references('notification_id')->on('notifications')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'notification_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('show_notification');
    }
};
