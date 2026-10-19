<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_form_displays_seeded_categories(): void
    {
        $this->seed();
        $this->actingAs(User::query()->firstOrFail());

        $response = $this->get('/create-post');
        $technology = Category::query()
            ->where('category_type', 'Technology')
            ->firstOrFail();

        $response->assertOk();
        $response->assertSee('value="'.$technology->category_id.'"', false);
        $response->assertSeeText(['Technology', 'Sport', 'News & Updates']);
    }
}
