<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->string('adjustment_number', 40)->unique();
            $table->foreignId('warehouse_stock_id')->constrained('warehouse_stocks')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedBigInteger('system_quantity');
            $table->unsignedBigInteger('physical_quantity');
            $table->bigInteger('difference_quantity');
            $table->string('movement_type', 40);
            $table->string('reason', 60)->index();
            $table->string('status', 20)->default('processed')->index();
            $table->text('notes');
            $table->timestamp('adjustment_date')->index();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('stock_movement_id')->nullable()->unique()->constrained('stock_movements')->restrictOnDelete();
            $table->timestamps();

            $table->index(['warehouse_id', 'adjustment_date']);
            $table->index(['product_variant_id', 'adjustment_date']);
            $table->index(['warehouse_stock_id', 'adjustment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
