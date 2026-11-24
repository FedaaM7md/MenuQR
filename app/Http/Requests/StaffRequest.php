<?php

/*
 * MenuQR - StaffRequest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-staff') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $staff = $this->route('staff');
        $staffId = $staff instanceof User ? $staff->id : null;

        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:120', Rule::unique('users', 'email')->ignore($staffId)],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => [$staffId ? 'nullable' : 'required', 'string', Password::min(8)->letters()->numbers()],
            'is_active' => ['nullable', 'boolean'],
            'locale' => ['nullable', Rule::in(['ar', 'en'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
