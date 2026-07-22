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
                if (!Schema::hasColumn('floorplans', 'company_profile_name')) {
                    $table->string('company_profile_name')->nullable()->after('company');
                }
                if (!Schema::hasColumn('floorplans', 'company_details')) {
                    $table->text('company_details')->nullable()->after('company_profile_name');
                }
                if (!Schema::hasColumn('floorplans', 'company_url')) {
                    $table->string('company_url')->nullable()->after('company_details');
                }
                if (!Schema::hasColumn('floorplans', 'company_logo')) {
                    $table->string('company_logo')->nullable()->after('company_url');
                }
                if (!Schema::hasColumn('floorplans', 'status')) {
                    $table->string('status')->default('pending')->after('networktype');
                }
                if (!Schema::hasColumn('floorplans', 'approval_message')) {
                    $table->text('approval_message')->nullable()->after('status');
                }
                if (!Schema::hasColumn('floorplans', 'approved_by')) {
                    $table->string('approved_by')->nullable()->after('approval_message');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('floorplans')) {
            Schema::table('floorplans', function (Blueprint $table) {
                foreach ([
                    'company_profile_name',
                    'company_details',
                    'company_url',
                    'company_logo',
                    'status',
                    'approval_message',
                    'approved_by',
                ] as $column) {
                    if (Schema::hasColumn('floorplans', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};