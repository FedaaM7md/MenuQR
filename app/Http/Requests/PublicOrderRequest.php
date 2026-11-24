<?php

/*
 * MenuQR - PublicOrderRequest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(Order::TYPES)],
            'customer_name' => ['nullable', 'string', 'max:80'],
            'customer_phone' => ['nullable', 'string', 'max:25', 'regex:/^\+?[0-9\s\-()]{7,25}$/'],
            'notes' => ['nullable', 'string', 'max:500'],
            // Honeypot: real customers never fill this hidden field.
            'website' => ['nullable', 'prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'website.prohibited' => __('menuqr.errors.spam_blocked'),
        ];
    }
}
