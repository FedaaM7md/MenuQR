<?php

/*
 * MenuQR - OptionGroupRequest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptionGroupRequest extends FormRequest
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
            'min_select' => ['required', 'integer', 'min:0', 'max:10'],
            'max_select' => ['required', 'integer', 'min:1', 'max:10', 'gte:min_select'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
