<?php

/*
 * MenuQR - CartController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function show(RestaurantTable $table): View
    {
        abort_unless($table->is_active, 404);

        $preview = $this->cart->preview($table);
        $settings = setting();

        return view('menu.cart', [
            'table' => $table,
            'preview' => $preview,
            'settings' => $settings,
            'isOpen' => $settings->isOpenNow(),
        ]);
    }

    public function add(AddToCartRequest $request, RestaurantTable $table): RedirectResponse
    {
        abort_unless($table->is_active, 404);

        $item = MenuItem::query()->with('optionGroups.options')->findOrFail($request->integer('item_id'));

        if (! $item->is_available) {
            throw ValidationException::withMessages([
                'cart' => __('menuqr.errors.item_unavailable', ['item' => $item->name()]),
            ]);
        }

        $this->cart->add(
            $table,
            $item,
            $request->integer('quantity'),
            $request->input('options', []) ?? [],
            $request->input('notes'),
        );

        return redirect()
            ->route('menu.show', $table->token)
            ->with('status', __('menuqr.menu.added_to_cart', ['item' => $item->name()]));
    }

    public function update(Request $request, RestaurantTable $table, string $line): RedirectResponse
    {
        abort_unless($table->is_active, 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->updateQuantity($table, $line, (int) $validated['quantity']);

        return redirect()->route('menu.cart', $table->token)->with('status', __('menuqr.cart.updated'));
    }

    public function remove(RestaurantTable $table, string $line): RedirectResponse
    {
        abort_unless($table->is_active, 404);

        $this->cart->remove($table, $line);

        return redirect()->route('menu.cart', $table->token)->with('status', __('menuqr.cart.removed'));
    }

    public function clear(RestaurantTable $table): RedirectResponse
    {
        abort_unless($table->is_active, 404);

        $this->cart->clear($table);

        return redirect()->route('menu.cart', $table->token)->with('status', __('menuqr.cart.cleared'));
    }
}
