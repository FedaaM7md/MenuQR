<?php

/*
 * MenuQR - OrderWorkflow.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class OrderWorkflow
{
    /**
     * Statuses a given user may move the order to right now.
     *
     * @return array<int, string>
     */
    public function allowedFor(Order $order, User $user): array
    {
        $possible = Order::TRANSITIONS[$order->status] ?? [];

        return array_values(array_filter($possible, function (string $target) use ($order, $user) {
            return $this->roleMay($order, $target, $user);
        }));
    }

    public function transition(Order $order, string $target, User $user): Order
    {
        if (! in_array($target, Order::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => __('menuqr.errors.invalid_status')]);
        }

        if (! $order->canTransitionTo($target)) {
            throw ValidationException::withMessages([
                'status' => __('menuqr.errors.invalid_transition', [
                    'from' => $order->statusLabel(),
                    'to' => __('menuqr.statuses.'.$target),
                ]),
            ]);
        }

        if (! $this->roleMay($order, $target, $user)) {
            throw ValidationException::withMessages(['status' => __('menuqr.errors.role_transition')]);
        }

        $order->status = $target;

        $timestamp = match ($target) {
            Order::STATUS_PREPARING => 'preparing_at',
            Order::STATUS_READY => 'ready_at',
            Order::STATUS_SERVED => 'served_at',
            Order::STATUS_CANCELLED => 'cancelled_at',
            default => null,
        };

        if ($timestamp !== null) {
            $order->{$timestamp} = now();
        }

        $order->save();

        return $order;
    }

    private function roleMay(Order $order, string $target, User $user): bool
    {
        if ($user->isAdmin() || $user->isCashier()) {
            return true;
        }

        if ($user->isKitchen()) {
            return ($order->status === Order::STATUS_NEW && $target === Order::STATUS_PREPARING)
                || ($order->status === Order::STATUS_PREPARING && $target === Order::STATUS_READY);
        }

        return false;
    }
}
