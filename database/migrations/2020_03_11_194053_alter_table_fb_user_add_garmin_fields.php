<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFbUserAddGarminFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_User', function (Blueprint $table) {
            $table->string('garmin_userId')->nullable();
            $table->string('garmin_userAccessToken')->nullable();
            $table->dateTime('garmin_registered_at')->nullable();
            $table->dateTime('garmin_revoked_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_User', function (Blueprint $table) {
            $table->dropColumn('garmin_userId');
            $table->dropColumn('garmin_userAccessToken');
            $table->dropColumn('garmin_registered_at');
            $table->dropColumn('garmin_revoked_at');
        });
    }
}
