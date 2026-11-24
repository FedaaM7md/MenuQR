<?php

/*
 * MenuQR — menu item validation.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Requests;

use App\Support\DemoMode;
use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-menu') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name_ar' => ['required', 'string', 'max:100'],
            'name_en' => ['required', 'string', 'max:100'],
            'description_ar' => ['nullable', 'string', 'max:600'],
            'description_en' => ['nullable', 'string', 'max:600'],
            'allergens_ar' => ['nullable', 'string', 'max:160'],
            'allergens_en' => ['nullable', 'string', 'max:160'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.DemoMode::uploadMaxKb()],
            'is_available' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_available' => $this->boolean('is_available'),
            'is_featured' => $this->boolean('is_featured'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
