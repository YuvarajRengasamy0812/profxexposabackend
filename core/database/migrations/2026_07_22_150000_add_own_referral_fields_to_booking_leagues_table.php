<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_leagues')) {
            return;
        }

        Schema::table('booking_leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_leagues', 'own_referral_code')) {
                $table->string('own_referral_code', 50)->nullable()->unique()->after('referrer_name');
            }

            if (!Schema::hasColumn('booking_leagues', 'own_referral_link')) {
                $table->string('own_referral_link', 500)->nullable()->after('own_referral_code');
            }

            if (!Schema::hasColumn('booking_leagues', 'referred_by_league_id')) {
                $table->unsignedBigInteger('referred_by_league_id')->nullable()->after('referred_by_user_id');
            }
        });

        DB::table('booking_leagues')
            ->where(function ($query) {
                $query->whereNull('own_referral_code')->orWhere('own_referral_code', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($leagueUsers) {
                foreach ($leagueUsers as $leagueUser) {
                    $code = 'PFXL' . str_pad((string) $leagueUser->id, 6, '0', STR_PAD_LEFT);

                    DB::table('booking_leagues')
                        ->where('id', $leagueUser->id)
                        ->update([
                            'own_referral_code' => $code,
                            'own_referral_link' => 'https://profxexpo.com/africa/LeagueEnroll?ref=' . $code,
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('booking_leagues')) {
            return;
        }

        Schema::table('booking_leagues', function (Blueprint $table) {
            foreach (['referred_by_league_id', 'own_referral_link', 'own_referral_code'] as $column) {
                if (Schema::hasColumn('booking_leagues', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
