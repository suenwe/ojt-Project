<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE categories DROP CONSTRAINT IF EXISTS categories_category_type_check');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $allowedCategories = [
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

        $hasCustomCategories = DB::table('categories')
            ->whereNotIn('category_type', $allowedCategories)
            ->exists();

        if ($hasCustomCategories) {
            throw new RuntimeException('Remove custom categories before restoring the original category restriction.');
        }

        $values = implode(', ', array_map(
            fn (string $category): string => DB::connection()->getPdo()->quote($category),
            $allowedCategories,
        ));

        DB::statement("ALTER TABLE categories ADD CONSTRAINT categories_category_type_check CHECK (category_type IN ($values))");
    }
};
