<?php

/*
 * MenuQR — audit logger.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Audit
{
    /**
     * Record a sensitive action without ever storing secrets or full payloads.
     *
     * @param  array<string, scalar|null>  $meta
     */
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $meta = []): ActivityLog
    {
        /** @var User|null $user */
        $user = Auth::user();
        $request = request();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description ? mb_substr($description, 0, 255) : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
            'meta' => $meta === [] ? null : $meta,
            'created_at' => now(),
        ]);
    }
}
