<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recharge_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('recharge_settings', 'm_sanction_load_r')) {
                $table->decimal('m_sanction_load_r', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('recharge_settings', 'm_sanction_load_y')) {
                $table->decimal('m_sanction_load_y', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('recharge_settings', 'm_sanction_load_b')) {
                $table->decimal('m_sanction_load_b', 10, 2)->nullable();
            }

            if (!Schema::hasColumn('recharge_settings', 'dg_sanction_load_r')) {
                $table->decimal('dg_sanction_load_r', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('recharge_settings', 'dg_sanction_load_y')) {
                $table->decimal('dg_sanction_load_y', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('recharge_settings', 'dg_sanction_load_b')) {
                $table->decimal('dg_sanction_load_b', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('recharge_settings', function (Blueprint $table) {
            if (Schema::hasColumn('recharge_settings', 'm_sanction_load_r')) {
                $table->dropColumn('m_sanction_load_r');
            }
            if (Schema::hasColumn('recharge_settings', 'm_sanction_load_y')) {
                $table->dropColumn('m_sanction_load_y');
            }
            if (Schema::hasColumn('recharge_settings', 'm_sanction_load_b')) {
                $table->dropColumn('m_sanction_load_b');
            }

            if (Schema::hasColumn('recharge_settings', 'dg_sanction_load_r')) {
                $table->dropColumn('dg_sanction_load_r');
            }
            if (Schema::hasColumn('recharge_settings', 'dg_sanction_load_y')) {
                $table->dropColumn('dg_sanction_load_y');
            }
            if (Schema::hasColumn('recharge_settings', 'dg_sanction_load_b')) {
                $table->dropColumn('dg_sanction_load_b');
            }
        });
    }
};
