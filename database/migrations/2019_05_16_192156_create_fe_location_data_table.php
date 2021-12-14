<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeLocationDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FE_LocationData', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->softDeletes();
            $table->bigInteger('FB_Workout_id')->unsigned();

            $table->double('latitude');
            $table->double('longitude');
            $table->double('altitude');
            $table->double('speed')->default(0);
            $table->dateTime('ctime');

        });

        Schema::table('FE_LocationData', function (Blueprint $table) {
            $table->foreign('FB_Workout_id')->references('id')->on('FB_Workout');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FE_LocationData', function (Blueprint $table) {
            $table->dropForeign('FB_Workout_id');
        });
        Schema::dropIfExists('FE_LocationData');
    }
}
