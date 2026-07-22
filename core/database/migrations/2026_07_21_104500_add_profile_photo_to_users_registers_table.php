<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users_registers') && !Schema::hasColumn('users_registers', 'profile_photo')) {
            Schema::table('users_registers', function (Blueprint $table) {
                $table->string('profile_photo')->nullable()->after('products_services');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users_registers') && Schema::hasColumn('users_registers', 'profile_photo')) {
            Schema::table('users_registers', function (Blueprint $table) {
                $table->dropColumn('profile_photo');
            });
        }
    }
};