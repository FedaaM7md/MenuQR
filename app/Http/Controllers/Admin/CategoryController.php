<?php

/*
 * MenuQR - CategoryController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-menu');

        $categories = Category::query()
            ->withCount('items')
            ->ordered()
            ->get();

        return view('admin.categories.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        $this->authorize('manage-menu');

        return view('admin.categories.form', ['category' => new Category(['is_active' => true])]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());
        Audit::log('menu.category_created', $category, $category->name_ar);

        return redirect()->route('admin.categories.index')->with('status', __('menuqr.categories.created'));
    }

    public function edit(Category $category): View
    {
        $this->authorize('manage-menu');

        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());
        Audit::log('menu.category_updated', $category, $category->name_ar);

        return redirect()->route('admin.categories.index')->with('status', __('menuqr.categories.updated'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('manage-menu');

        if ($category->items()->exists()) {
            return back()->withErrors(['category' => __('menuqr.categories.not_empty')]);
        }

        $category->delete();
        Audit::log('menu.category_deleted', null, $category->name_ar);

        return redirect()->route('admin.categories.index')->with('status', __('menuqr.categories.deleted'));
    }
}
