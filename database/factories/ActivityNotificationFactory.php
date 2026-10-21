<?php

namespace Database\Factories;

use App\Models\ActivityNotification;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityNotification>
 */
class ActivityNotificationFactory extends Factory
{
    /**
     * Define the model's default state: someone liked a post by the recipient.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'actor_id' => User::factory(),
            'type' => ActivityNotification::TYPE_REACTION,
            'post_id' => fn (array $attributes): int => Post::query()->create([
                'descriptions' => fake()->sentence(),
                'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
                'post_type' => 'Discussion',
                'state' => 'published',
                'date_time' => now()->subHour(),
                'user_id' => $attributes['user_id'],
            ])->post_id,
            'comment_id' => null,
            'reaction_type' => 'like',
            'read_at' => null,
        ];
    }

    /**
     * The notification has already been seen.
     */
    public function read(): static
    {
        return $this->state(fn (): array => ['read_at' => now()]);
    }
}
