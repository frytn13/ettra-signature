<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan role internal untuk membedakan Owner dan Admin.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)
                ->nullable()
                ->index()
                ->after('account_type');
        });

        /*
         * Project versi sebelumnya belum memiliki kolom role. Jika database hanya
         * memiliki satu akun internal aktif, akun tersebut merupakan akun Owner
         * awal yang dibuat melalui command ettra:create-owner sehingga aman untuk
         * ditetapkan otomatis sebagai Owner.
         *
         * Jika terdapat lebih dari satu akun internal, migration tidak menebak role.
         * Gunakan command ettra:assign-owner untuk menetapkan akun Owner secara jelas.
         */
        $internalUsers = DB::table('users')
            ->where('account_type', 'internal')
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($internalUsers->count() === 1) {
            DB::table('users')
                ->where('id', $internalUsers->first())
                ->update(['role' => 'owner']);
        }
    }

    /**
     * Menghapus kolom role ketika migration dibatalkan.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
