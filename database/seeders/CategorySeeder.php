<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
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
            'Music',
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate([
                'category_type' => $category,
            ]);
        }
    }
}
