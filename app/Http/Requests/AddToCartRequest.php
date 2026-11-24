<?php

/*
 * MenuQR - AddToCartRequest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'options' => ['nullable', 'array'],
            'options.*' => ['integer', 'distinct'],
            'notes' => ['nullable', 'string', 'max:200'],
        ];
    }
}
