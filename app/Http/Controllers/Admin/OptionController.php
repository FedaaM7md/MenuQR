<?php

/*
 * MenuQR - OptionController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptionRequest;
use App\Models\MenuItem;
use App\Models\MenuOption;
use App\Models\OptionGroup;
use App\Services\Audit;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;

class OptionController extends Controller
{
    public function store(OptionRequest $request, MenuItem $item, OptionGroup $group): RedirectResponse
    {
        $this->ensureBelongs($item, $group);

        $data = $request->validated();
        $data['price_delta_minor'] = Money::toMinor($data['price_delta']);
        unset($data['price_delta']);
        $data['sort_order'] = $data['sort_order'] ?? ((int) $group->options()->max('sort_order') + 1);

        $option = $group->options()->create($data);
        Audit::log('menu.option_created', $option, $option->name_ar);

        return redirect()
            ->route('admin.items.options.index', $item)
            ->with('status', __('menuqr.options.option_created'));
    }

    public function update(OptionRequest $request, MenuItem $item, OptionGroup $group, MenuOption $option): RedirectResponse
    {
        $this->ensureBelongs($item, $group);
        abort_unless($option->option_group_id === $group->id, 404);

        $data = $request->validated();
        $data['price_delta_minor'] = Money::toMinor($data['price_delta']);
        unset($data['price_delta']);

        $option->update($data);
        Audit::log('menu.option_updated', $option, $option->name_ar);

        return redirect()
            ->route('admin.items.options.index', $item)
            ->with('status', __('menuqr.options.option_updated'));
    }

    public function destroy(MenuItem $item, OptionGroup $group, MenuOption $option): RedirectResponse
    {
        $this->authorize('manage-menu');
        $this->ensureBelongs($item, $group);
        abort_unless($option->option_group_id === $group->id, 404);

        $option->delete();
        Audit::log('menu.option_deleted', null, $option->name_ar);

        return redirect()
            ->route('admin.items.options.index', $item)
            ->with('status', __('menuqr.options.option_deleted'));
    }

    private function ensureBelongs(MenuItem $item, OptionGroup $group): void
    {
        abort_unless($group->menu_item_id === $item->id, 404);
    }
}
