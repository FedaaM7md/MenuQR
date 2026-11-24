<?php

/*
 * MenuQR — admin overview.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Services\OrderWorkflow;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ReportService $reports, OrderWorkflow $workflow): View
    {
        $this->authorize('manage-settings');

        $today = now()->startOfDay();
        $todayOrders = Order::query()->where('placed_at', '>=', $today)->notCancelled();

        $stats = [
            'orders_today' => (clone $todayOrders)->count(),
            'revenue_today' => (int) (clone $todayOrders)->sum('total_minor'),
            'active_orders' => Order::query()->active()->count(),
            'average_today' => 0,
        ];

        $stats['average_today'] = $stats['orders_today'] > 0
            ? (int) round($stats['revenue_today'] / $stats['orders_today'])
            : 0;

        $summary = $reports->summary(now()->subDays(6)->startOfDay(), now());
        $maxDay = max(1, (int) ($summary['by_day']->max('revenue_minor') ?? 0));

        $activeOrders = Order::query()
            ->with('table')
            ->active()
            ->orderBy('placed_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'summary' => $summary,
            'maxDay' => $maxDay,
            'activeOrders' => $activeOrders,
            'workflow' => $workflow,
            'recentActivity' => ActivityLog::query()->with('user')->orderByDesc('id')->limit(6)->get(),
        ]);
    }
}
