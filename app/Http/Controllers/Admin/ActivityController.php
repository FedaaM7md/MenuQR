<?php

/*
 * MenuQR — activity log.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view-reports');

        $logs = ActivityLog::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(40);

        return view('admin.activity.index', ['logs' => $logs]);
    }
}
