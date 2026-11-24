<?php

/*
 * MenuQR - 2026_11_24_000200_add_allergens_to_menu_items.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('allergens_ar', 160)->nullable()->after('description_en');
            $table->string('allergens_en', 160)->nullable()->after('allergens_ar');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['allergens_ar', 'allergens_en']);
        });
    }
};
