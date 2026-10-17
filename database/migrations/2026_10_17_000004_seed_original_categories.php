<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
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
        ];

        DB::table('categories')->insertOrIgnore(array_map(
            fn (string $category): array => ['category_type' => $category],
            $categories,
        ));
    }

    public function down(): void
    {
        // Keep category data on rollback; the rows may already be in use by posts.
    }
};
