<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_access_logs', function (Blueprint $table): void {
            $table->bigIncrements('access_log_id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('browser')->nullable();
            $table->string('method', 10);
            $table->string('path', 1024);
            $table->string('route_name')->nullable();
            $table->timestamp('accessed_at')->useCurrent()->index();

            $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_access_logs');
    }
};
