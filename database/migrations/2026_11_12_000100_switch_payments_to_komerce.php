<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pembayaran pindah dari Midtrans ke Komerce: satu order bisa punya beberapa percobaan bayar
 * (QR kedaluwarsa, ganti bank), dan detail VA/QRIS disimpan di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Foreign key butuh index biasa sebelum unique-nya dilepas.
            $table->index('order_id');
            $table->dropUnique(['order_id']);

            $table->renameColumn('midtrans_order_id', 'reference');
            $table->renameIndex('payments_midtrans_order_id_unique', 'payments_reference_unique');
            $table->dropColumn('snap_token');

            $table->string('provider_payment_id', 100)->nullable()->unique()->after('reference');
            $table->string('channel_code', 50)->nullable()->after('payment_type');
            $table->string('bank_name', 100)->nullable()->after('channel_code');
            $table->string('va_number', 50)->nullable()->after('bank_name');
            $table->text('qr_string')->nullable()->after('va_number');
            $table->string('payment_url')->nullable()->after('qr_string');
            $table->unsignedInteger('amount')->default(0)->after('payment_url');
            $table->dateTime('expires_at')->nullable()->after('status');
            $table->boolean('needs_refund')->default(false)->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['provider_payment_id', 'channel_code', 'bank_name', 'va_number', 'qr_string', 'payment_url', 'amount', 'expires_at', 'needs_refund']);
            $table->string('snap_token')->nullable()->after('reference');
            $table->renameIndex('payments_reference_unique', 'payments_midtrans_order_id_unique');
            $table->renameColumn('reference', 'midtrans_order_id');
            $table->unique('order_id');
            $table->dropIndex(['order_id']);
        });
    }
};
