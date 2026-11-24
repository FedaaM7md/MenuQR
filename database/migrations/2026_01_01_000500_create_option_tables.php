<?php

/*
 * MenuQR - 2026_01_01_000500_create_option_tables.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en');
            $table->unsignedTinyInteger('min_select')->default(0);
            $table->unsignedTinyInteger('max_select')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['menu_item_id', 'sort_order']);
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_group_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en');
            $table->integer('price_delta_minor')->default(0);
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['option_group_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('options');
        Schema::dropIfExists('option_groups');
    }
};
