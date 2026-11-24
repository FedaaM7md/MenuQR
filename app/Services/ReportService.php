<?php

/*
 * MenuQR - ReportService.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * @return array{
     *     totals: array{orders: int, revenue_minor: int, items: int, average_minor: int},
     *     by_day: Collection,
     *     by_item: Collection,
     *     by_category: Collection,
     *     statuses: Collection
     * }
     */
    public function summary(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $fromDate = $from->format('Y-m-d 00:00:00');
        $toDate = $to->format('Y-m-d 23:59:59');

        $base = Order::query()
            ->whereBetween('placed_at', [$fromDate, $toDate])
            ->where('status', '!=', Order::STATUS_CANCELLED);

        $totals = [
            'orders' => (clone $base)->count(),
            'revenue_minor' => (int) (clone $base)->sum('total_minor'),
            'items' => (int) DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.placed_at', [$fromDate, $toDate])
                ->where('orders.status', '!=', Order::STATUS_CANCELLED)
                ->sum('order_items.quantity'),
            'average_minor' => 0,
        ];

        $totals['average_minor'] = $totals['orders'] > 0
            ? (int) round($totals['revenue_minor'] / $totals['orders'])
            : 0;

        $byDay = (clone $base)
            ->selectRaw('date(placed_at) as day, count(*) as orders, sum(total_minor) as revenue_minor')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $byItem = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.placed_at', [$fromDate, $toDate])
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->groupBy('order_items.menu_item_id', 'order_items.name_ar', 'order_items.name_en')
            ->selectRaw('order_items.menu_item_id, order_items.name_ar, order_items.name_en, sum(order_items.quantity) as quantity, sum(order_items.line_total_minor) as revenue_minor')
            ->orderByDesc('quantity')
            ->get();

        $byCategory = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->join('categories', 'categories.id', '=', 'menu_items.category_id')
            ->whereBetween('orders.placed_at', [$fromDate, $toDate])
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->groupBy('categories.id', 'categories.name_ar', 'categories.name_en')
            ->selectRaw('categories.id, categories.name_ar, categories.name_en, sum(order_items.quantity) as quantity, sum(order_items.line_total_minor) as revenue_minor')
            ->orderByDesc('revenue_minor')
            ->get();

        $statuses = Order::query()
            ->whereBetween('placed_at', [$fromDate, $toDate])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        return [
            'totals' => $totals,
            'by_day' => $byDay,
            'by_item' => $byItem,
            'by_category' => $byCategory,
            'statuses' => $statuses,
        ];
    }
}
