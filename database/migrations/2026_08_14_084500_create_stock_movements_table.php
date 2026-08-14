<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_number', 40)->unique();
            $table->foreignId('warehouse_stock_id')->constrained('warehouse_stocks')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->string('movement_type', 40)->index();
            $table->string('direction', 12)->index();
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('quantity_before');
            $table->unsignedBigInteger('quantity_after');
            $table->unsignedBigInteger('quantity_reserved_before')->default(0);
            $table->unsignedBigInteger('quantity_reserved_after')->default(0);
            $table->unsignedBigInteger('quantity_damaged_before')->default(0);
            $table->unsignedBigInteger('quantity_damaged_after')->default(0);
            $table->unsignedBigInteger('quantity_available_before')->default(0);
            $table->unsignedBigInteger('quantity_available_after')->default(0);
            $table->string('reference_type', 80)->nullable()->index();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('movement_date')->index();
            $table->timestamps();

            $table->index(['warehouse_id', 'movement_date']);
            $table->index(['product_variant_id', 'movement_date']);
            $table->index(['warehouse_stock_id', 'movement_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
