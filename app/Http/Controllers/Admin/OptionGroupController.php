<?php

/*
 * MenuQR - OptionGroupController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptionGroupRequest;
use App\Models\MenuItem;
use App\Models\OptionGroup;
use App\Services\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OptionGroupController extends Controller
{
    public function index(MenuItem $item): View
    {
        $this->authorize('manage-menu');
        $item->load(['optionGroups.options', 'category']);

        return view('admin.items.options', ['item' => $item]);
    }

    public function store(OptionGroupRequest $request, MenuItem $item): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? ((int) $item->optionGroups()->max('sort_order') + 1);

        $group = $item->optionGroups()->create($data);
        Audit::log('menu.option_group_created', $group, $group->name_ar);

        return redirect()
            ->route('admin.items.options.index', $item)
            ->with('status', __('menuqr.options.group_created'));
    }

    public function update(OptionGroupRequest $request, MenuItem $item, OptionGroup $group): RedirectResponse
    {
        $this->ensureBelongs($item, $group);
        $group->update($request->validated());
        Audit::log('menu.option_group_updated', $group, $group->name_ar);

        return redirect()
            ->route('admin.items.options.index', $item)
            ->with('status', __('menuqr.options.group_updated'));
    }

    public function destroy(MenuItem $item, OptionGroup $group): RedirectResponse
    {
        $this->authorize('manage-menu');
        $this->ensureBelongs($item, $group);

        $group->delete();
        Audit::log('menu.option_group_deleted', null, $group->name_ar);

        return redirect()
            ->route('admin.items.options.index', $item)
            ->with('status', __('menuqr.options.group_deleted'));
    }

    private function ensureBelongs(MenuItem $item, OptionGroup $group): void
    {
        abort_unless($group->menu_item_id === $item->id, 404);
    }
}
