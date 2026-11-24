<?php

/*
 * MenuQR - OptionRequest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-menu') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:80'],
            'name_en' => ['required', 'string', 'max:80'],
            'price_delta' => ['required', 'numeric', 'min:-9999', 'max:9999'],
            'is_available' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_available' => $this->boolean('is_available'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
