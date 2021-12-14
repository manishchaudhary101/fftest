<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeDerivedDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FE_DerivedData', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->softDeletes();
            $table->bigInteger('FB_Workout_id')->unsigned();

            $table->dateTime('dtime');
            $table->integer('dtype');
            $table->float('dvalue');

        });

        Schema::table('FE_DerivedData', function (Blueprint $table) {
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
        Schema::table('FE_DerivedData', function (Blueprint $table) {
            $table->dropForeign('FB_Workout_id');
        });
        Schema::dropIfExists('FE_DerivedData');
    }
}
