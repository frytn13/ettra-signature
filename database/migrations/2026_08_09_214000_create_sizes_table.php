<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat master ukuran untuk variasi produk Ettra Signature.
     */
    public function up(): void
    {
        Schema::create('sizes', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 80)->unique();
            $table->unsignedSmallInteger('sort_order')->default(1)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Menghapus tabel ukuran apabila migration di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('sizes');
    }
};
