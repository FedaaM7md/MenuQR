<?php

/*
 * MenuQR - 2026_01_01_000400_create_menu_items_table.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('price_minor'); // EGP piasters
            $table->string('image_path')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'sort_order']);
            $table->index(['is_available', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
