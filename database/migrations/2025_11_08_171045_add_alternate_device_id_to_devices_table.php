<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    if (!Schema::hasColumn('sites', 'alternate_device_id')) {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('alternate_device_id')->nullable()->after('device_id');
        });
    }
}

public function down()
{
    if (Schema::hasColumn('sites', 'alternate_device_id')) {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('alternate_device_id');
        });
    }
}
};
