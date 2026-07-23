<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_speakers') && !Schema::hasColumn('client_speakers', 'display_order')) {
            Schema::table('client_speakers', function (Blueprint $table) {
                $table->unsignedInteger('display_order')->default(0)->after('photo')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('client_speakers') && Schema::hasColumn('client_speakers', 'display_order')) {
            Schema::table('client_speakers', function (Blueprint $table) {
                $table->dropColumn('display_order');
            });
        }
    }
};
