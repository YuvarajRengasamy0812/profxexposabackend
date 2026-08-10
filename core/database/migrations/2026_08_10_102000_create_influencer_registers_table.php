<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('influencer_registers')) {
            return;
        }

        Schema::create('influencer_registers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone', 50);
            $table->string('nationality')->nullable();
            $table->string('company_name')->nullable();
            $table->string('position_role')->nullable();
            $table->string('password');
            $table->string('profile_photo')->nullable();
            $table->string('referral_code', 50)->nullable()->unique();
            $table->string('referral_link', 500)->nullable();
            $table->string('submitted_referral_code', 50)->nullable();
            $table->unsignedBigInteger('referred_by_influencer_id')->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->text('approval_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencer_registers');
    }
};
