<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat master warna untuk variasi produk Ettra Signature.
     */
    public function up(): void
    {
        Schema::create('colors', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 12)->unique();
            $table->string('name', 80)->unique();
            $table->string('hex_code', 7)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Menghapus tabel warna apabila migration di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('colors');
    }
};
