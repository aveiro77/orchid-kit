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
            $table->index('status_aktif');
            $table->index('kota');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('status');
            $table->index('tanggal_mulai');
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->index('pemberi_referral_id');
            $table->index('penerima_referral_id');
            $table->index('status');
        });

        Schema::table('opportunities', function (Blueprint $table) {
            $table->index('tanggal_expired');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status_aktif']);
            $table->dropIndex(['kota']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['tanggal_mulai']);
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex(['pemberi_referral_id']);
            $table->dropIndex(['penerima_referral_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropIndex(['tanggal_expired']);
        });
    }
};
