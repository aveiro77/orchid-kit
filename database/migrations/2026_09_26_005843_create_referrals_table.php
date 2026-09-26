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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemberi_referral_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('penerima_referral_id')->constrained('users')->cascadeOnDelete();
            $table->string('client_name');
            $table->string('project_name');
            $table->decimal('nilai_estimasi', 15, 2)->default(0);
            $table->enum('status', ['introduced', 'follow_up', 'negotiation', 'won', 'lost'])->default('introduced');
            $table->text('catatan')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['pemberi_referral_id', 'penerima_referral_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
