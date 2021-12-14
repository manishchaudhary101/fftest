<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFbWorkoutTableAddTimezoneColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->dateTimeTz('start_time_utc')->nullable();
            $table->dateTimeTz('start_time_local')->nullable();
            $table->dateTimeTz('end_time_utc')->nullable();
            $table->integer('time_zone_utc_offset')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->dropColumn('start_time_utc');
            $table->dropColumn('start_time_local');
            $table->dropColumn('end_time_utc');
            $table->dropColumn('time_zone_utc_offset');
        });
    }
}
