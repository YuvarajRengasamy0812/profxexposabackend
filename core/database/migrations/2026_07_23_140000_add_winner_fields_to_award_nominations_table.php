<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('award_nominations')) {
            Schema::table('award_nominations', function (Blueprint $table) {
                if (!Schema::hasColumn('award_nominations', 'winner_note')) {
                    $table->text('winner_note')->nullable()->after('status');
                }
                if (!Schema::hasColumn('award_nominations', 'winner_selected_at')) {
                    $table->timestamp('winner_selected_at')->nullable()->after('winner_note');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('award_nominations')) {
            Schema::table('award_nominations', function (Blueprint $table) {
                if (Schema::hasColumn('award_nominations', 'winner_note')) {
                    $table->dropColumn('winner_note');
                }
                if (Schema::hasColumn('award_nominations', 'winner_selected_at')) {
                    $table->dropColumn('winner_selected_at');
                }
            });
        }
    }
};