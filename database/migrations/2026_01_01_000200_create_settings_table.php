<?php

/*
 * MenuQR - 2026_01_01_000200_create_settings_table.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('restaurant_name_ar');
            $table->string('restaurant_name_en');
            $table->string('tagline_ar')->nullable();
            $table->string('tagline_en')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('currency', 8)->default('EGP');
            $table->unsignedInteger('tax_percent')->default(0); // basis points: 1400 = 14%
            $table->unsignedInteger('service_percent')->default(0); // basis points
            $table->string('whatsapp_number', 25)->nullable();
            $table->json('hours')->nullable();
            $table->string('address_ar')->nullable();
            $table->string('address_en')->nullable();
            $table->boolean('is_accepting_orders')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
