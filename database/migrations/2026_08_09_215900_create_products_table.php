<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel produk dasar Ettra Signature.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->unsignedInteger('category_sequence')->nullable();
            $table->string('code', 40)->unique();
            $table->string('name', 180)->index();
            $table->string('slug', 220)->unique();
            $table->text('description')->nullable();
            $table->decimal('initial_purchase_price', 15, 2)->nullable();
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2);
            $table->string('status', 20)->default('active')->index();
            $table->string('availability_status', 20)->default('available')->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->date('entry_date')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['category_id', 'category_sequence'], 'products_category_sequence_unique');
            $table->index(['category_id', 'status'], 'products_category_status_index');
            $table->index(['status', 'is_visible'], 'products_status_visible_index');
        });
    }

    /**
     * Menghapus tabel produk ketika migration di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
