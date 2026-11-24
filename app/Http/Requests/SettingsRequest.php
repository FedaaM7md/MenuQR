<?php

/*
 * MenuQR — restaurant settings validation.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Requests;

use App\Models\Setting;
use App\Support\DemoMode;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-settings') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'restaurant_name_ar' => ['required', 'string', 'max:80'],
            'restaurant_name_en' => ['required', 'string', 'max:80'],
            'tagline_ar' => ['nullable', 'string', 'max:120'],
            'tagline_en' => ['nullable', 'string', 'max:120'],
            'address_ar' => ['nullable', 'string', 'max:160'],
            'address_en' => ['nullable', 'string', 'max:160'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.DemoMode::uploadMaxKb()],
            'tax_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'service_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'whatsapp_number' => ['nullable', 'string', 'max:25', 'regex:/^\+?[0-9\s\-()]{8,25}$/'],
            'is_accepting_orders' => ['nullable', 'boolean'],
            'hours' => ['nullable', 'array'],
            'hours.*.open' => ['nullable', 'date_format:H:i'],
            'hours.*.close' => ['nullable', 'date_format:H:i'],
            'hours.*.closed' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $hours = $this->input('hours');
        $normalized = [];

        foreach (array_keys(Setting::defaultHours()) as $day) {
            $row = is_array($hours) ? ($hours[$day] ?? []) : [];
            $normalized[$day] = [
                'open' => $row['open'] ?? '10:00',
                'close' => $row['close'] ?? '23:00',
                'closed' => filter_var($row['closed'] ?? false, FILTER_VALIDATE_BOOL),
            ];
        }

        $this->merge(['hours' => $normalized]);
    }
}
