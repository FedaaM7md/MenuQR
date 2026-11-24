<?php

/*
 * MenuQR - KitchenController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderWorkflow;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function index(Request $request, OrderWorkflow $workflow): View
    {
        $this->authorize('view-kitchen');

        $orders = Order::query()
            ->with(['items.options', 'table'])
            ->active()
            ->orderBy('placed_at')
            ->get();

        return view('staff.kitchen.index', [
            'orders' => $orders,
            'workflow' => $workflow,
        ]);
    }

    public function feed(Request $request, OrderWorkflow $workflow): JsonResponse
    {
        $this->authorize('view-kitchen');

        $orders = Order::query()
            ->with(['items.options', 'table'])
            ->active()
            ->orderBy('placed_at')
            ->get();

        $user = $request->user();

        $payload = $orders->map(function (Order $order) use ($user, $workflow) {
            return [
                'id' => $order->id,
                'number' => $order->number,
                'table' => $order->table?->name ?? '—',
                'type' => $order->typeLabel(),
                'type_key' => $order->type,
                'status' => $order->status,
                'status_label' => $order->statusLabel(),
                'placed_at' => $order->placed_at->toIso8601String(),
                'minutes_waiting' => max(0, (int) $order->placed_at->diffInMinutes(now())),
                'total' => money($order->total_minor),
                'notes' => $order->notes,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->name(),
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                    'options' => $item->options->map(fn ($option) => [
                        'group' => $option->groupName(),
                        'name' => $option->optionName(),
                        'delta' => Money::format($option->price_delta_minor),
                    ])->values(),
                ])->values(),
                'transitions' => array_map(fn (string $status) => [
                    'value' => $status,
                    'label' => __('menuqr.statuses.'.$status),
                ], $workflow->allowedFor($order, $user)),
            ];
        });

        return response()->json([
            'orders' => $payload,
            'counts' => [
                'new' => $orders->where('status', Order::STATUS_NEW)->count(),
                'preparing' => $orders->where('status', Order::STATUS_PREPARING)->count(),
                'ready' => $orders->where('status', Order::STATUS_READY)->count(),
            ],
            'server_time' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store, private');
    }
}
