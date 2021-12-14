<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFrUserHasDeviceRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FR_User_has_Device', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('biostrip_macid',36)->unique()->index();
            $table->string('serial_number',50)->unique()->index();
            $table->bigInteger('FB_User_id');
            $table->dateTimeTz('last_sync_on_ios')->nullable();
            $table->dateTimeTz('last_sync_on_android')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FR_User_has_Device');
    }
}
