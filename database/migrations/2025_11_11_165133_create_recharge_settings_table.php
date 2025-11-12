<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recharge_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('m_site_id')->index();
            $table->decimal('m_recharge_amount', 10, 2)->nullable();
            $table->decimal('m_fixed_charge', 10, 2)->nullable();
            $table->decimal('m_unit_charge', 10, 2)->nullable();
            $table->decimal('m_sanction_load', 10, 2)->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('m_site_id')
                  ->references('id')
                  ->on('sites')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharge_settings');
    }
};
