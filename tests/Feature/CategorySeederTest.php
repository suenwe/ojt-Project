<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Keyword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_form_displays_seeded_categories(): void
    {
        $this->seed();
        $this->withoutVite();

        $response = $this->actingAs(User::query()->firstOrFail())->get('/create-post');
        $technology = Category::query()
            ->where('category_type', 'Technology')
            ->firstOrFail();

        $response->assertOk();
        $response->assertSee('value="'.$technology->category_id.'"', false);
        $response->assertSeeText(['Technology', 'Sport', 'News & Updates']);
    }

    public function test_database_seeder_includes_added_categories_and_keywords(): void
    {
        $this->seed();

        $this->assertDatabaseHas('categories', ['category_type' => 'Music']);

        foreach ([
            'Fuck',
            'ကုလား',
            'ဘင်္ဂါလီ',
            'သာသနာဖျက်',
            'စုန်းမ',
            'စောက်',
            'ရိုက်',
            'သတ်',
            'မီးရှို့',
            'Kill Yourself',
            'Die',
            'Suicide',
        ] as $keyword) {
            $this->assertDatabaseHas('keywords', ['keyword_text' => $keyword]);
        }

        $this->seed();

        $this->assertSame(12, Keyword::query()->count());
        $this->assertSame(1, User::query()->where('email', 'test@example.com')->count());
    }
}
