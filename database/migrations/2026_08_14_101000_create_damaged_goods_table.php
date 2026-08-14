<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('damaged_goods', function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_number', 40)->unique();
            $table->foreignId('warehouse_stock_id')->constrained('warehouse_stocks')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->enum('action', ['mark_damaged', 'recover']);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('damaged_before')->default(0);
            $table->unsignedInteger('damaged_after')->default(0);
            $table->unsignedInteger('available_before')->default(0);
            $table->unsignedInteger('available_after')->default(0);
            $table->string('reason', 100);
            $table->text('notes')->nullable();
            $table->dateTime('transaction_date');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('stock_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->timestamps();
            $table->index(['warehouse_id', 'transaction_date']);
            $table->index(['product_variant_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damaged_goods');
    }
};
