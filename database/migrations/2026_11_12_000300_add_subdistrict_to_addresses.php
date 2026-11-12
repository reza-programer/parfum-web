<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kelurahan/desa dari RajaOngkir (/destination/sub-district/{district_id}).
 * Nullable: alamat lama tetap sah dan bisa dilengkapi saat diubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedInteger('subdistrict_id')->nullable()->after('district_name');
            $table->string('subdistrict_name')->nullable()->after('subdistrict_id');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['subdistrict_id', 'subdistrict_name']);
        });
    }
};
