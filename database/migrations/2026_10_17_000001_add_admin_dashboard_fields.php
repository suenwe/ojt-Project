<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->rememberToken()->after('password');
            $table->string('browser')->nullable()->after('IP_address');
            $table->timestamp('banned_at')->nullable()->after('birthday');
            $table->text('ban_reason')->nullable()->after('banned_at');
        });

        Schema::table('reports', function (Blueprint $table): void {
            $table->string('status', 20)->default('pending')->after('reason');
            $table->timestamp('reviewed_at')->nullable()->after('status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            $table->foreign('reviewed_by')->references('user_id')->on('users')->nullOnDelete();
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('category_type', 100)->change();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE categories DROP CONSTRAINT IF EXISTS categories_category_type_check');
        }

        DB::table('categories')->insertOrIgnore(array_map(
            fn (string $category): array => ['category_type' => $category],
            $this->originalCategories(),
        ));
    }

    public function down(): void
    {
        $originalCategories = $this->originalCategories();

        if (DB::table('categories')->whereNotIn('category_type', $originalCategories)->exists()) {
            throw new RuntimeException('Remove categories added outside the original category list before rolling back this migration.');
        }

        Schema::table('categories', function (Blueprint $table) use ($originalCategories): void {
            $table->enum('category_type', $originalCategories)->change();
        });

        Schema::table('reports', function (Blueprint $table): void {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['status', 'reviewed_at', 'reviewed_by']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['remember_token', 'browser', 'banned_at', 'ban_reason']);
        });
    }

    /**
     * @return list<string>
     */
    private function originalCategories(): array
    {
        return [
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
    }
};
