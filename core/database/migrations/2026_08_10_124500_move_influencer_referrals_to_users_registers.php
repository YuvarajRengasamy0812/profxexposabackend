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
                if (!Schema::hasColumn('users_registers', 'influencer_referral_code')) {
                    $table->string('influencer_referral_code', 50)->nullable()->after('referral_link')->index();
                }

                if (!Schema::hasColumn('users_registers', 'referred_by_influencer_id')) {
                    $table->unsignedBigInteger('referred_by_influencer_id')->nullable()->after('influencer_referral_code')->index();
                }
            });
        }

        if (Schema::hasTable('influencer_user_referrals')) {
            DB::table('influencer_user_referrals')
                ->orderBy('id')
                ->chunkById(100, function ($referrals) {
                    foreach ($referrals as $referral) {
                        DB::table('users_registers')
                            ->where('id', $referral->user_register_id)
                            ->update([
                                'referred_by_influencer_id' => $referral->influencer_register_id,
                                'influencer_referral_code' => $referral->referral_code,
                            ]);
                    }
                });

            Schema::dropIfExists('influencer_user_referrals');
        }

        if (Schema::hasTable('influencer_registers')) {
            DB::table('influencer_registers')
                ->whereNotNull('referral_link')
                ->where('referral_link', 'like', '%/Influencers%')
                ->update([
                    'referral_link' => DB::raw("REPLACE(referral_link, '/Influencers', '/Register')"),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users_registers')) {
            Schema::table('users_registers', function (Blueprint $table) {
                if (Schema::hasColumn('users_registers', 'referred_by_influencer_id')) {
                    $table->dropColumn('referred_by_influencer_id');
                }

                if (Schema::hasColumn('users_registers', 'influencer_referral_code')) {
                    $table->dropColumn('influencer_referral_code');
                }
            });
        }
    }
};
