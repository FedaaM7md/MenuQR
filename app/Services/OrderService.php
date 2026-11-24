<?php

/*
 * MenuQR - OrderService.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSequence;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PricingService $pricing,
    ) {}

    /**
     * Place an order from the table's session cart. Every price, option and tax
     * figure is recomputed server-side inside a transaction.
     *
     * @param  array{type: string, customer_name?: ?string, customer_phone?: ?string, notes?: ?string}  $data
     */
    public function place(RestaurantTable $table, array $data, ?User $creator = null): Order
    {
        $rawLines = $this->cart->lines($table);

        if ($rawLines === []) {
            throw ValidationException::withMessages(['cart' => __('menuqr.errors.cart_empty')]);
        }

        $order = DB::transaction(function () use ($table, $data, $creator, $rawLines) {
            $prepared = [];
            $subtotal = 0;

            foreach ($rawLines as $key => $line) {
                $item = MenuItem::query()
                    ->with('optionGroups.options')
                    ->find((int) $line['item_id']);

                if (! $item || ! $item->is_available) {
                    throw ValidationException::withMessages([
                        'cart' => __('menuqr.errors.item_unavailable', ['item' => $item?->name() ?? '#'.$line['item_id']]),
                    ]);
                }

                $priced = $this->pricing->priceLine(
                    $item,
                    (int) $line['quantity'],
                    $line['options'] ?? [],
                );

                $prepared[$key] = [
                    'priced' => $priced,
                    'notes' => $line['notes'] ?? null,
                ];
                $subtotal += $priced['line_total_minor'];
            }

            $config = setting();
            $tax = Money::percentOf($subtotal, $config->tax_percent);
            $service = Money::percentOf($subtotal, $config->service_percent);
            $placedAt = Carbon::now();
            $number = $this->nextNumber((int) $placedAt->format('Y'));

            $order = Order::create([
                'number' => $number,
                'restaurant_table_id' => $table->id,
                'type' => $data['type'],
                'status' => Order::STATUS_NEW,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'subtotal_minor' => $subtotal,
                'tax_percent' => $config->tax_percent,
                'tax_minor' => $tax,
                'service_percent' => $config->service_percent,
                'service_minor' => $service,
                'total_minor' => $subtotal + $tax + $service,
                'created_by' => $creator?->id,
                'placed_at' => $placedAt,
            ]);

            foreach ($prepared as $row) {
                /** @var array $priced */
                $priced = $row['priced'];
                /** @var MenuItem $item */
                $item = $priced['item'];

                /** @var OrderItem $line */
                $line = $order->items()->create([
                    'menu_item_id' => $item->id,
                    'name_ar' => $item->name_ar,
                    'name_en' => $item->name_en,
                    'unit_price_minor' => $priced['unit_base_minor'],
                    'quantity' => $priced['quantity'],
                    'line_total_minor' => $priced['line_total_minor'],
                    'notes' => $row['notes'],
                ]);

                foreach ($priced['options'] as $choice) {
                    $line->options()->create([
                        'group_name_ar' => $choice['group']->name_ar,
                        'group_name_en' => $choice['group']->name_en,
                        'option_name_ar' => $choice['option']->name_ar,
                        'option_name_en' => $choice['option']->name_en,
                        'price_delta_minor' => $choice['option']->price_delta_minor,
                    ]);
                }
            }

            return $order;
        });

        $this->cart->clear($table);

        $ids = session()->get('placed_orders', []);
        $ids[] = $order->id;
        session()->put('placed_orders', array_values(array_unique($ids)));

        return $order;
    }

    public function nextNumber(int $year): string
    {
        $sequence = OrderSequence::query()->firstOrCreate(
            ['year' => $year],
            ['last_number' => 0],
        );

        $sequence = OrderSequence::query()->whereKey($sequence->id)->lockForUpdate()->firstOrFail();
        $sequence->last_number++;
        $sequence->save();

        return Order::formatNumber($year, $sequence->last_number);
    }
}
