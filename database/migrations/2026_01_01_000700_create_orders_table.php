<?php

/*
 * MenuQR - 2026_01_01_000700_create_orders_table.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 32)->unique();
            $table->foreignId('restaurant_table_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20)->default('dine_in'); // dine_in | takeaway
            $table->string('status', 20)->default('new'); // new | preparing | ready | served | cancelled
            $table->string('customer_name', 80)->nullable();
            $table->string('customer_phone', 25)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('subtotal_minor');
            $table->unsignedInteger('tax_percent'); // basis points snapshot
            $table->unsignedInteger('tax_minor');
            $table->unsignedInteger('service_percent'); // basis points snapshot
            $table->unsignedInteger('service_minor');
            $table->unsignedInteger('total_minor');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('placed_at');
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'placed_at']);
            $table->index('placed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
