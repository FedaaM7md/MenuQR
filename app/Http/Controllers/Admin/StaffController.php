<?php

/*
 * MenuQR — staff and role management.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffRequest;
use App\Models\User;
use App\Services\Audit;
use App\Support\DemoMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-staff');

        $staff = User::query()->orderByRaw("case role when 'admin' then 0 when 'cashier' then 1 else 2 end")->orderBy('name')->get();

        return view('admin.staff.index', ['staff' => $staff]);
    }

    public function create(): View
    {
        $this->authorize('manage-staff');

        return view('admin.staff.form', ['member' => new User(['role' => User::ROLE_CASHIER, 'is_active' => true])]);
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $this->authorize('manage-staff');

        $validated = $request->validated();

        if (DemoMode::active() && $validated['role'] === User::ROLE_ADMIN) {
            return back()->withInput()->withErrors(['role' => __('menuqr.demo.admin_forbidden')]);
        }

        $member = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'password' => $validated['password'],
            'is_active' => $request->boolean('is_active'),
            'is_demo' => false,
            'locale' => $validated['locale'] ?? null,
        ]);

        Audit::log('staff.created', $member, $member->email, ['role' => $member->role]);

        return redirect()->route('admin.staff.index')->with('status', __('menuqr.staff.created', ['name' => $member->name]));
    }

    public function edit(User $staff): View
    {
        $this->authorize('manage-staff');

        return view('admin.staff.form', ['member' => $staff]);
    }

    public function update(StaffRequest $request, User $staff): RedirectResponse
    {
        $this->authorize('manage-staff');

        $validated = $request->validated();
        $isActive = $request->boolean('is_active');

        // Demo accounts are shared: identity, role, status and password stay fixed.
        if ($staff->isDemo()) {
            $validated['email'] = $staff->email;
            $validated['role'] = $staff->role;
            unset($validated['password']);
            $isActive = true;
        }

        if (DemoMode::active() && $validated['role'] === User::ROLE_ADMIN && ! $staff->isAdmin()) {
            return back()->withErrors(['role' => __('menuqr.demo.admin_forbidden')]);
        }

        if ($staff->id === $request->user()->id && ! $isActive) {
            return back()->withErrors(['is_active' => __('menuqr.staff.cannot_deactivate_self')]);
        }

        if ($staff->isAdmin() && ($validated['role'] !== User::ROLE_ADMIN || ! $isActive)) {
            if (User::query()->where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1) {
                return back()->withErrors(['role' => __('menuqr.staff.last_admin')]);
            }
        }

        $staff->fill([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'is_active' => $isActive,
            'locale' => $validated['locale'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $staff->password = $validated['password'];
        }

        $staff->save();

        Audit::log('staff.updated', $staff, $staff->email, ['role' => $staff->role, 'active' => $staff->is_active]);

        return redirect()->route('admin.staff.index')->with('status', __('menuqr.staff.updated'));
    }

    public function destroy(User $staff): RedirectResponse
    {
        $this->authorize('manage-staff');

        if ($staff->isDemo()) {
            return back()->withErrors(['staff' => __('menuqr.demo.account_locked')]);
        }

        if ($staff->id === auth()->id()) {
            return back()->withErrors(['staff' => __('menuqr.staff.cannot_delete_self')]);
        }

        if (DemoMode::active() && $staff->isAdmin()) {
            return back()->withErrors(['staff' => __('menuqr.demo.admin_forbidden')]);
        }

        if ($staff->isAdmin() && User::query()->where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['staff' => __('menuqr.staff.last_admin')]);
        }

        $staff->delete();
        Audit::log('staff.deleted', null, $staff->email, ['role' => $staff->role]);

        return redirect()->route('admin.staff.index')->with('status', __('menuqr.staff.deleted'));
    }
}
