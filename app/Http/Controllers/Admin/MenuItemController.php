<?php

/*
 * MenuQR — menu item management.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuItemRequest;
use App\Models\Category;
use App\Models\MenuItem;
use App\Services\Audit;
use App\Services\ImageUploader;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function __construct(private readonly ImageUploader $images) {}

    public function index(Request $request): View
    {
        $this->authorize('manage-menu');

        $search = trim((string) $request->query('q', ''));

        $items = MenuItem::query()
            ->with('category')
            ->withCount('optionGroups')
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(fn ($inner) => $inner
                    ->where('name_ar', 'like', $term)
                    ->orWhere('name_en', 'like', $term));
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->query('availability') === 'on', fn ($query) => $query->where('is_available', true))
            ->when($request->query('availability') === 'off', fn ($query) => $query->where('is_available', false))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.items.index', [
            'items' => $items,
            'categories' => Category::query()->ordered()->get(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $this->authorize('manage-menu');

        return view('admin.items.form', [
            'item' => new MenuItem(['is_available' => true, 'sort_order' => 0]),
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function store(MenuItemRequest $request): RedirectResponse
    {
        $data = $this->payload($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->images->store($request->file('image'), 'menu-items');
        }

        $item = MenuItem::create($data);
        Audit::log('menu.item_created', $item, $item->name_ar);

        return redirect()
            ->route('admin.items.options.index', $item)
            ->with('status', __('menuqr.items.created'));
    }

    public function edit(MenuItem $item): View
    {
        $this->authorize('manage-menu');

        return view('admin.items.form', [
            'item' => $item,
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function update(MenuItemRequest $request, MenuItem $item): RedirectResponse
    {
        $data = $this->payload($request);

        if ($request->hasFile('image')) {
            $newImage = $this->images->store($request->file('image'), 'menu-items');

            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }

            $data['image_path'] = $newImage;
        }

        $item->update($data);
        Audit::log('menu.item_updated', $item, $item->name_ar);

        return redirect()
            ->route('admin.items.edit', $item)
            ->with('status', __('menuqr.items.updated'));
    }

    public function toggle(MenuItem $item): RedirectResponse
    {
        $this->authorize('manage-menu');

        $item->update(['is_available' => ! $item->is_available]);
        Audit::log('menu.item_availability', $item, $item->is_available ? __('menuqr.common.enabled') : __('menuqr.common.disabled'));

        return back()
            ->with('status', __('menuqr.items.availability_changed'))
            ->with('undo', [
                'url' => route('admin.items.toggle', $item),
                'method' => 'PATCH',
                'label' => __('menuqr.common.undo'),
            ]);
    }

    public function destroy(MenuItem $item): RedirectResponse
    {
        $this->authorize('manage-menu');

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();
        Audit::log('menu.item_deleted', null, $item->name_ar);

        return redirect()->route('admin.items.index')->with('status', __('menuqr.items.deleted'));
    }

    /** @return array<string, mixed> */
    private function payload(MenuItemRequest $request): array
    {
        $validated = $request->validated();

        return [
            'category_id' => $validated['category_id'],
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'description_ar' => $validated['description_ar'] ?? null,
            'description_en' => $validated['description_en'] ?? null,
            'allergens_ar' => $validated['allergens_ar'] ?? null,
            'allergens_en' => $validated['allergens_en'] ?? null,
            'price_minor' => Money::toMinor($validated['price']),
            'is_available' => $request->boolean('is_available'),
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }
}
