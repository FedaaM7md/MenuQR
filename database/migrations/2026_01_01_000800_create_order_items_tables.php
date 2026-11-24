<?php

/*
 * MenuQR - 2026_01_01_000800_create_order_items_tables.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_ar');
            $table->string('name_en');
            $table->unsignedInteger('unit_price_minor'); // base price before options
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('line_total_minor');
            $table->string('notes', 200)->nullable();
            $table->timestamps();
        });

        Schema::create('order_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('group_name_ar');
            $table->string('group_name_en');
            $table->string('option_name_ar');
            $table->string('option_name_en');
            $table->integer('price_delta_minor');
            $table->timestamps();
        });

        Schema::create('order_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_options');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('order_sequences');
    }
};
