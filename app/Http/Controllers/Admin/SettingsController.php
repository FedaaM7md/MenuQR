<?php

/*
 * MenuQR — restaurant settings.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsRequest;
use App\Models\Setting;
use App\Services\Audit;
use App\Services\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly ImageUploader $images) {}

    public function edit(): View
    {
        $this->authorize('manage-settings');

        return view('admin.settings.edit', ['settings' => Setting::current()]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $settings = Setting::current();
        $validated = $request->validated();

        $data = [
            'restaurant_name_ar' => $validated['restaurant_name_ar'],
            'restaurant_name_en' => $validated['restaurant_name_en'],
            'tagline_ar' => $validated['tagline_ar'] ?? null,
            'tagline_en' => $validated['tagline_en'] ?? null,
            'address_ar' => $validated['address_ar'] ?? null,
            'address_en' => $validated['address_en'] ?? null,
            'currency' => 'EGP',
            'tax_percent' => (int) round(((float) $validated['tax_percent']) * 100),
            'service_percent' => (int) round(((float) $validated['service_percent']) * 100),
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
            'hours' => $validated['hours'] ?? Setting::defaultHours(),
            'is_accepting_orders' => $request->boolean('is_accepting_orders'),
        ];

        if ($request->hasFile('logo')) {
            $path = $this->images->store($request->file('logo'), 'branding', 'logo');

            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $data['logo_path'] = $path;
        }

        if ($request->boolean('remove_logo') && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $data['logo_path'] = null;
        }

        $settings->update($data);

        Audit::log('settings.updated', $settings, $settings->restaurant_name_en, [
            'tax_percent' => $settings->tax_percent,
            'service_percent' => $settings->service_percent,
            'accepting_orders' => $settings->is_accepting_orders,
        ]);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', __('menuqr.settings.saved'));
    }
}
