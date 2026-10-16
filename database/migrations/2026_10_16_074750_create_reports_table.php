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
        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('report_id');

            $table->unsignedBigInteger('reporter_id');
            $table->unsignedBigInteger('target_id');

            $table->enum('report_type', [
                'Problem involving someone under 18',
                'Bullying, harassment or abuse',
                'Suicide or self-harm',
                'Violent, hateful or disturbing content',
                'Selling or promoting restricted items',
                'Adult content',
                'Scam, fraud or false information',
                'Intellectual property',
                "I don''t want to see this",
            ]);

            $table->text('reason')->nullable();

            $table->foreign('reporter_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();
            
            $table->foreign('target_id')
                ->references('post_id')->on('posts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
