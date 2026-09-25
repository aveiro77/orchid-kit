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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_category_id')->constrained()->cascadeOnDelete();
            $table->string('nama_usaha');
            $table->text('deskripsi')->nullable();
            $table->string('alamat')->nullable();
            $table->string('website')->nullable();
            $table->enum('status', ['aktif', 'non_aktif'])->default('aktif');
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('business_category_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
