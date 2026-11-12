<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Konsentrasi parfum (EDP, EDT, dan seterusnya) untuk filter katalog.
 * Nullable: produk lama dibiarkan kosong sampai diisi admin, tidak ditebak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('concentration', 20)->nullable()->after('scent_family')->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['concentration']);
            $table->dropColumn('concentration');
        });
    }
};
