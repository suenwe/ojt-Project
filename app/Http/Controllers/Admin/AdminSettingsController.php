<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminSettingsController extends Controller
{
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_type' => ['required', 'string', 'max:100', 'unique:categories,category_type'],
        ]);

        Category::query()->create($validated);

        return $this->backTo('categories', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'category_type' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'category_type')->ignore($category->category_id, 'category_id'),
            ],
        ]);

        $category->update($validated);

        return $this->backTo('categories', 'Category updated.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        $category->delete();

        return $this->backTo('categories', 'Category removed. Existing posts in this category will remain uncategorized.');
    }

    public function storeKeyword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'keyword_text' => ['required', 'string', 'max:255', 'unique:keywords,keyword_text'],
        ]);

        Keyword::query()->create($validated);

        return $this->backTo('restricted', 'Restricted text added.');
    }

    public function destroyKeyword(Keyword $keyword): RedirectResponse
    {
        $keyword->delete();

        return $this->backTo('restricted', 'Restricted text removed.');
    }

    public function storeNotification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notification_type' => ['required', 'string', 'max:255'],
            'notification_text' => ['required', 'string', 'max:5000'],
        ]);

        Notification::query()->create($validated);

        return $this->backTo('notifications', 'Notification template created.');
    }

    public function updateNotification(Request $request, Notification $notification): RedirectResponse
    {
        $validated = $request->validate([
            'notification_type' => ['required', 'string', 'max:255'],
            'notification_text' => ['required', 'string', 'max:5000'],
        ]);

        $notification->update($validated);

        return $this->backTo('notifications', 'Notification template updated.');
    }

    public function destroyNotification(Notification $notification): RedirectResponse
    {
        $notification->delete();

        return $this->backTo('notifications', 'Notification template removed.');
    }

    private function backTo(string $section, string $message): RedirectResponse
    {
        return to_route('admin.dashboard', ['section' => $section])->with('status', $message);
    }
}
