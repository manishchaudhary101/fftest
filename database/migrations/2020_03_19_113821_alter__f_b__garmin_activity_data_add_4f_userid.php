<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFBGarminActivityDataAdd4fUserid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //FB_GarminActivityData
        Schema::table('FB_GarminActivityData', function (Blueprint $table) {
            $table->bigInteger('FB_User_id')->nullable();
            $table->bigInteger('FB_Workout_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_GarminActivityData', function (Blueprint $table) {
            $table->dropColumn('FB_User_id');
            $table->dropColumn('FB_Workout_id');
        });
    }
}
