<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('award_nominations')) {
            Schema::create('award_nominations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('name');
                $table->string('email')->index();
                $table->string('company')->nullable();
                $table->string('phone')->nullable();
                $table->string('website')->nullable();
                $table->string('category')->index();
                $table->string('award_title')->index();
                $table->text('reason')->nullable();
                $table->string('status')->default('pending')->index();
                $table->timestamps();

                $table->unique(['email', 'award_title'], 'award_nominations_email_award_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('award_nominations');
    }
};