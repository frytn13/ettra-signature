<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_number',40)->unique();
            $table->enum('channel',['online','offline']);
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name',150);
            $table->string('customer_phone',30)->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('subtotal',15,2)->default(0);
            $table->decimal('discount_total',15,2)->default(0);
            $table->decimal('shipping_cost',15,2)->default(0);
            $table->decimal('grand_total',15,2)->default(0);
            $table->enum('payment_method',['cash','bank_transfer','qris']);
            $table->enum('payment_status',['unpaid','waiting_verification','paid','rejected','refunded'])->default('unpaid');
            $table->enum('order_status',['waiting_payment','processing','packed','shipped','completed','cancelled'])->default('waiting_payment');
            $table->text('notes')->nullable();
            $table->dateTime('transaction_date');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['channel','transaction_date']);
            $table->index(['payment_status','order_status']);
        });

        Schema::create('sales_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('sku_snapshot',100);
            $table->string('product_name_snapshot',180);
            $table->string('variant_snapshot',180)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price',15,2);
            $table->decimal('discount_amount',15,2)->default(0);
            $table->decimal('subtotal',15,2);
            $table->decimal('cost_price_snapshot',15,2)->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->string('payment_number',40)->unique();
            $table->enum('method',['cash','bank_transfer','qris']);
            $table->decimal('amount',15,2);
            $table->enum('status',['pending','verified','rejected','refunded'])->default('pending');
            $table->string('proof_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status','method']);
        });

        Schema::create('shipments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sales_order_id')->unique()->constrained('sales_orders')->cascadeOnDelete();
            $table->string('courier',100)->nullable();
            $table->string('tracking_number',100)->nullable();
            $table->enum('status',['pending','packed','in_transit','delivered'])->default('pending');
            $table->dateTime('packed_at')->nullable();
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shipment_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->enum('status',['pending','packed','in_transit','delivered']);
            $table->string('description',255)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_histories');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};
