<?php

/*
 * MenuQR - ReportTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    private function makeOrder(string $status, string $placedAt, array $lines): Order
    {
        $order = Order::factory()->create([
            'status' => $status,
            'placed_at' => Carbon::parse($placedAt),
            'subtotal_minor' => 0,
            'tax_minor' => 0,
            'total_minor' => 0,
            'service_minor' => 0,
        ]);

        $subtotal = 0;

        foreach ($lines as [$item, $quantity]) {
            $lineTotal = $item->price_minor * $quantity;
            $subtotal += $lineTotal;
            $order->items()->create([
                'menu_item_id' => $item->id,
                'name_ar' => $item->name_ar,
                'name_en' => $item->name_en,
                'unit_price_minor' => $item->price_minor,
                'quantity' => $quantity,
                'line_total_minor' => $lineTotal,
            ]);
        }

        $tax = (int) round($subtotal * 1400 / 10000);
        $order->update([
            'subtotal_minor' => $subtotal,
            'tax_minor' => $tax,
            'total_minor' => $subtotal + $tax,
        ]);

        return $order->refresh();
    }

    public function test_report_aggregates_by_day_item_and_category_and_excludes_cancelled(): void
    {
        $catA = $this->makeCategory(['name_ar' => 'أطباق', 'name_en' => 'Mains']);
        $catB = $this->makeCategory(['name_ar' => 'مشروبات', 'name_en' => 'Drinks']);
        $itemA = $this->makeItem(['category_id' => $catA->id, 'name_ar' => 'كفتة', 'name_en' => 'Kofta', 'price_minor' => 10000]);
        $itemB = $this->makeItem(['category_id' => $catB->id, 'name_ar' => 'عصير', 'name_en' => 'Juice', 'price_minor' => 5000]);

        $this->makeOrder('served', '2026-01-10 12:00:00', [[$itemA, 2], [$itemB, 1]]);
        $this->makeOrder('cancelled', '2026-01-11 12:00:00', [[$itemA, 5]]);
        $this->makeOrder('preparing', '2026-01-12 12:00:00', [[$itemB, 1]]);

        $summary = app(ReportService::class)->summary(
            Carbon::parse('2026-01-10 00:00:00'),
            Carbon::parse('2026-01-12 23:59:59'),
        );

        $this->assertSame(2, $summary['totals']['orders']);
        $this->assertSame(34200, $summary['totals']['revenue_minor']);
        $this->assertSame(4, $summary['totals']['items']);
        $this->assertSame(17100, $summary['totals']['average_minor']);

        $this->assertCount(2, $summary['by_day']);
        $this->assertSame(28500, (int) $summary['by_day']->firstWhere('day', '2026-01-10')->revenue_minor);

        $kofta = $summary['by_item']->firstWhere('name_en', 'Kofta');
        $this->assertSame(2, (int) $kofta->quantity);
        $this->assertSame(20000, (int) $kofta->revenue_minor);

        $juice = $summary['by_item']->firstWhere('name_en', 'Juice');
        $this->assertSame(2, (int) $juice->quantity);
        $this->assertSame(10000, (int) $juice->revenue_minor);

        $byCategory = $summary['by_category']->keyBy('name_en');
        $this->assertSame(20000, (int) $byCategory['Mains']->revenue_minor);
        $this->assertSame(10000, (int) $byCategory['Drinks']->revenue_minor);
    }

    public function test_report_respects_the_date_range(): void
    {
        $item = $this->makeItem(['price_minor' => 7000]);
        $this->makeOrder('served', '2026-02-01 10:00:00', [[$item, 1]]);
        $this->makeOrder('served', '2026-02-05 10:00:00', [[$item, 2]]);

        $summary = app(ReportService::class)->summary(
            Carbon::parse('2026-02-05 00:00:00'),
            Carbon::parse('2026-02-05 23:59:59'),
        );

        $this->assertSame(1, $summary['totals']['orders']);
        $this->assertSame(15960, $summary['totals']['revenue_minor']); // 14000 + 14% tax
        $this->assertSame(2, $summary['totals']['items']);
    }

    public function test_admin_can_view_the_report_page_and_others_cannot(): void
    {
        $item = $this->makeItem(['price_minor' => 20000]);
        $this->makeOrder('served', '2026-03-01 12:00:00', [[$item, 1]]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['from' => '2026-03-01', 'to' => '2026-03-01']))
            ->assertOk()
            ->assertSee('228.00')
            ->assertSee($item->name_ar);

        $this->actingAs(User::factory()->cashier()->create())
            ->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs(User::factory()->kitchen()->create())
            ->get(route('admin.reports.index'))->assertForbidden();
    }

    public function test_invalid_report_dates_are_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.reports.index', ['from' => 'not-a-date']))
            ->assertSessionHasErrors('from');
    }
}
