<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('floorplans')) {
            Schema::table('floorplans', function (Blueprint $table) {
                if (!Schema::hasColumn('floorplans', 'booth_design')) {
                    $table->string('booth_design')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('floorplans', 'booth_design_image')) {
                    $table->string('booth_design_image')->nullable()->after('booth_design');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('floorplans')) {
            Schema::table('floorplans', function (Blueprint $table) {
                foreach (['booth_design_image', 'booth_design'] as $column) {
                    if (Schema::hasColumn('floorplans', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
