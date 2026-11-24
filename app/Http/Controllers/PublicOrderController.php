<?php

/*
 * MenuQR - PublicOrderController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers;

use App\Http\Requests\PublicOrderRequest;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Services\Audit;
use App\Services\OrderService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicOrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function store(PublicOrderRequest $request, RestaurantTable $table): RedirectResponse
    {
        abort_unless($table->is_active, 404);

        if (! setting()->is_accepting_orders) {
            throw ValidationException::withMessages([
                'cart' => __('menuqr.errors.not_accepting_orders'),
            ]);
        }

        $order = $this->orders->place($table, [
            'type' => $request->string('type')->toString(),
            'customer_name' => $request->input('customer_name') ?: null,
            'customer_phone' => $request->input('customer_phone') ?: null,
            'notes' => $request->input('notes') ?: null,
        ]);

        Audit::log('order.placed', $order, $order->number, ['table' => $table->name]);

        return redirect()
            ->route('menu.orders.show', [$table->token, $order->number])
            ->with('status', __('menuqr.order.placed'));
    }

    public function show(Request $request, RestaurantTable $table, Order $order): View
    {
        abort_unless($table->is_active, 404);
        abort_unless($order->restaurant_table_id === $table->id, 404);

        $placed = $request->session()->get('placed_orders', []);
        abort_unless(in_array($order->id, $placed, true), 404);

        $order->load(['items.options', 'table']);
        $settings = setting();

        $summary = $order->items
            ->map(function ($item) {
                return '• '.$item->name().' × '.$item->quantity.' = '.Money::format($item->line_total_minor);
            })
            ->implode("\n");

        $message = __('menuqr.whatsapp.message', [
            'restaurant' => $settings->name(),
            'number' => $order->number,
            'table' => $order->table?->name ?? '-',
            'type' => $order->typeLabel(),
            'items' => $summary,
            'total' => Money::format($order->total_minor),
        ]);

        return view('menu.confirmation', [
            'table' => $table,
            'order' => $order,
            'settings' => $settings,
            'whatsappLink' => $settings->whatsappLink($message),
        ]);
    }
}
