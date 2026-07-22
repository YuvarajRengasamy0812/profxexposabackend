<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users_registers')) {
            Schema::table('users_registers', function (Blueprint $table) {
                if (!Schema::hasColumn('users_registers', 'referral_code')) {
                    $table->string('referral_code', 50)->nullable()->unique()->after('products_services');
                }

                if (!Schema::hasColumn('users_registers', 'referral_link')) {
                    $table->string('referral_link', 500)->nullable()->after('referral_code');
                }
            });

            DB::table('users_registers')
                ->where(function ($query) {
                    $query->whereNull('referral_code')->orWhere('referral_code', '');
                })
                ->orderBy('id')
                ->chunkById(100, function ($users) {
                    foreach ($users as $user) {
                        $code = 'PFX' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);

                        DB::table('users_registers')
                            ->where('id', $user->id)
                            ->update([
                                'referral_code' => $code,
                                'referral_link' => 'https://profxexpo.com/africa/LeagueEnroll?ref=' . $code,
                            ]);
                    }
                });
        }

        if (Schema::hasTable('booking_leagues')) {
            Schema::table('booking_leagues', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_leagues', 'referral_code')) {
                    $table->string('referral_code', 50)->nullable()->after('role');
                }

                if (!Schema::hasColumn('booking_leagues', 'referred_by_user_id')) {
                    $table->unsignedBigInteger('referred_by_user_id')->nullable()->after('referral_code');
                }

                if (!Schema::hasColumn('booking_leagues', 'referrer_name')) {
                    $table->string('referrer_name', 255)->nullable()->after('referred_by_user_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_leagues')) {
            Schema::table('booking_leagues', function (Blueprint $table) {
                foreach (['referrer_name', 'referred_by_user_id', 'referral_code'] as $column) {
                    if (Schema::hasColumn('booking_leagues', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('users_registers')) {
            Schema::table('users_registers', function (Blueprint $table) {
                foreach (['referral_link', 'referral_code'] as $column) {
                    if (Schema::hasColumn('users_registers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
