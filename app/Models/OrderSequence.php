<?php

/*
 * MenuQR - OrderSequence.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSequence extends Model
{
    protected $fillable = ['year', 'last_number'];

    protected function casts(): array
    {
        return ['year' => 'integer', 'last_number' => 'integer'];
    }
}
