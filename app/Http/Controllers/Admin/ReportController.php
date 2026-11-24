<?php

/*
 * MenuQR - sales reports and CSV export.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports): View
    {
        $this->authorize('view-reports');

        [$from, $to] = $this->range($request);
        $summary = $reports->summary($from, $to);
        $maxDay = max(1, (int) ($summary['by_day']->max('revenue_minor') ?? 0));

        return view('admin.reports.index', [
            'summary' => $summary,
            'maxDay' => $maxDay,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function export(Request $request, ReportService $reports): StreamedResponse
    {
        $this->authorize('view-reports');

        [$from, $to] = $this->range($request);
        $summary = $reports->summary($from, $to);
        $locale = app()->getLocale();

        $filename = 'menuqr-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($summary, $from, $to, $locale) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                __('menuqr.reports.period'),
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            ]);

            fputcsv($out, []);
            fputcsv($out, [__('menuqr.reports.by_day')]);
            fputcsv($out, [__('menuqr.reports.day'), __('menuqr.reports.orders_count'), __('menuqr.reports.revenue')]);
            foreach ($summary['by_day'] as $row) {
                fputcsv($out, [$this->cell($row->day), (int) $row->orders, $this->money($row->revenue_minor)]);
            }

            fputcsv($out, []);
            fputcsv($out, [__('menuqr.reports.by_item')]);
            fputcsv($out, [__('menuqr.reports.item'), __('menuqr.reports.quantity'), __('menuqr.reports.revenue')]);
            foreach ($summary['by_item'] as $row) {
                $name = $locale === 'ar' ? $row->name_ar : ($row->name_en ?: $row->name_ar);
                fputcsv($out, [$this->cell($name), (int) $row->quantity, $this->money($row->revenue_minor)]);
            }

            fputcsv($out, []);
            fputcsv($out, [__('menuqr.reports.by_category')]);
            fputcsv($out, [__('menuqr.reports.category'), __('menuqr.reports.quantity'), __('menuqr.reports.revenue')]);
            foreach ($summary['by_category'] as $row) {
                $name = $locale === 'ar' ? $row->name_ar : ($row->name_en ?: $row->name_ar);
                fputcsv($out, [$this->cell($name), (int) $row->quantity, $this->money($row->revenue_minor)]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function range(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : now()->subDays(29)->startOfDay();
        $to = isset($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(366)->startOfDay();
        }

        return [$from, $to];
    }

    private function money(int|string|null $minor): string
    {
        return number_format(((int) $minor) / 100, 2, '.', '');
    }

    /**
     * Prevent spreadsheet formula injection in exported text cells.
     */
    private function cell(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }
}
