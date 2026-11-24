<?php

/*
 * MenuQR - OrderController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Audit;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $orders = Order::query()
            ->with('table')
            ->withCount('items')
            ->when(is_string($status) && in_array($status, Order::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('number', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('customer_phone', 'like', $term)
                        ->orWhereHas('table', fn ($table) => $table->where('name', 'like', $term));
                });
            })
            ->when($request->filled('date'), fn ($query) => $query->whereDate('placed_at', $request->date('date')))
            ->orderByDesc('placed_at')
            ->paginate(20)
            ->withQueryString();

        $counts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('staff.orders.index', [
            'orders' => $orders,
            'counts' => $counts,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function show(Order $order, OrderWorkflow $workflow): View
    {
        $this->authorize('view', $order);

        $order->load(['items.options', 'table', 'creator']);

        return view('staff.orders.show', [
            'order' => $order,
            'workflow' => $workflow,
        ]);
    }

    public function updateStatus(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        $workflow->transition($order, $validated['status'], $request->user());
        Audit::log('order.status_changed', $order, $order->number, ['status' => $order->status]);

        return back()->with('status', __('menuqr.order.status_updated', [
            'number' => $order->number,
            'status' => $order->statusLabel(),
        ]));
    }
}
