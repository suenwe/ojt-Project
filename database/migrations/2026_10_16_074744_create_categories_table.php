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
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('category_id');

            $table->enum('category_type', [
                'Technology',
                'Education',
                'Gaming',
                'Entertainment',
                'Health & Fitness',
                'Business & Career',
                'Travel',
                'Art & Design',
                'Fashion & Lifestyle',
                'Food & Cooking',
                'Sport',
                'General Discussion',
                'Events & Activities',
                'Books and Literature',
                'News & Updates',
            ])->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
