<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFrUserHasDevicesTableAllowDuplicates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FR_User_has_Device', function (Blueprint $table) {
            $table->dropUnique('fr_user_has_device_biostrip_macid_unique');
            $table->dropUnique('fr_user_has_device_serial_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
