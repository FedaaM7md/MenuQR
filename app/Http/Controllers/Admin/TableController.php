<?php

/*
 * MenuQR - TableController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Models\RestaurantTable;
use App\Services\Audit;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TableController extends Controller
{
    public function __construct(private readonly QrCodeService $qr) {}

    public function index(): View
    {
        $this->authorize('manage-tables');

        $tables = RestaurantTable::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.tables.index', [
            'tables' => $tables,
            'qrCodes' => $tables->mapWithKeys(fn (RestaurantTable $table) => [
                $table->id => $this->qr->dataUri($table, 220),
            ]),
        ]);
    }

    public function store(TableRequest $request): RedirectResponse
    {
        $this->authorize('manage-tables');

        $table = RestaurantTable::create($request->validated());
        Audit::log('table.created', $table, $table->name);

        return redirect()->route('admin.tables.index')->with('status', __('menuqr.tables.created'));
    }

    public function update(TableRequest $request, RestaurantTable $table): RedirectResponse
    {
        $this->authorize('manage-tables');

        $table->update($request->validated());
        Audit::log('table.updated', $table, $table->name);

        return redirect()->route('admin.tables.index')->with('status', __('menuqr.tables.updated'));
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        $this->authorize('manage-tables');

        $table->delete();
        Audit::log('table.deleted', null, $table->name);

        return redirect()->route('admin.tables.index')->with('status', __('menuqr.tables.deleted'));
    }

    public function regenerate(RestaurantTable $table): RedirectResponse
    {
        $this->authorize('manage-tables');

        $table->regenerateToken();
        Audit::log('table.token_regenerated', $table, $table->name);

        return redirect()->route('admin.tables.index')->with('status', __('menuqr.tables.token_regenerated'));
    }

    public function qr(RestaurantTable $table): Response
    {
        $this->authorize('manage-tables');

        return response($this->qr->svg($table, 360), 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function print(): View
    {
        $this->authorize('manage-tables');

        $tables = RestaurantTable::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.tables.print', [
            'tables' => $tables,
            'qrCodes' => $tables->mapWithKeys(fn (RestaurantTable $table) => [
                $table->id => $this->qr->dataUri($table, 480),
            ]),
            'settings' => setting(),
        ]);
    }
}
