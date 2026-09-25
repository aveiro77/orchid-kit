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
            $table->string('foto')->nullable()->after('password');
            $table->string('nomor_wa')->nullable()->after('foto');
            $table->string('kota')->nullable()->after('nomor_wa');
            $table->text('bio')->nullable()->after('kota');
            $table->string('linkedin')->nullable()->after('bio');
            $table->string('website')->nullable()->after('linkedin');
            $table->string('instagram')->nullable()->after('website');
            $table->boolean('status_aktif')->default(true)->after('instagram');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'foto',
                'nomor_wa',
                'kota',
                'bio',
                'linkedin',
                'website',
                'instagram',
                'status_aktif',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
