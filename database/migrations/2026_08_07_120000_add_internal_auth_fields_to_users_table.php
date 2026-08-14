<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->unique()->after('email');
            $table->string('account_type', 20)->default('internal')->index()->after('password');
            $table->boolean('is_active')->default(true)->index()->after('account_type');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropIndex(['account_type']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'phone',
                'account_type',
                'is_active',
                'last_login_at',
                'last_login_ip',
                'deleted_at',
            ]);
        });
    }
};
