<?php

/*
 * MenuQR - PublicMenuController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RestaurantTable;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicMenuController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function show(Request $request, RestaurantTable $table): View
    {
        $this->ensureTableIsUsable($table);

        $search = trim((string) $request->query('q', ''));
        $search = mb_substr($search, 0, 60);

        $categories = Category::query()
            ->active()
            ->ordered()
            ->with(['items' => function ($query) use ($search) {
                $query->ordered()->with('optionGroups.options');

                if ($search !== '') {
                    $term = '%'.$search.'%';
                    $query->where(function ($inner) use ($term) {
                        $inner->where('name_ar', 'like', $term)
                            ->orWhere('name_en', 'like', $term)
                            ->orWhere('description_ar', 'like', $term)
                            ->orWhere('description_en', 'like', $term);
                    });
                }
            }])
            ->get()
            ->filter(fn (Category $category) => $category->items->isNotEmpty())
            ->values();

        $settings = setting();
        $preview = $this->cart->preview($table);

        return view('menu.show', [
            'table' => $table,
            'settings' => $settings,
            'categories' => $categories,
            'search' => $search,
            'cartCount' => $this->cart->count($table),
            'cartTotal' => (int) ($preview['total_minor'] ?? 0),
            'isOpen' => $settings->isOpenNow(),
        ]);
    }

    public function ensureTableIsUsable(RestaurantTable $table): void
    {
        abort_unless($table->is_active, 404);
    }
}
