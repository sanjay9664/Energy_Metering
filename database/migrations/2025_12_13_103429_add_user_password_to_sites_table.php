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
    Schema::table('sites', function (Blueprint $table) {
        $table->string('user_password')->nullable()->after('user_email');
    });
}

public function down()
{
    Schema::table('sites', function (Blueprint $table) {
        $table->dropColumn('user_password');
    });
}

};
