<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFbWorkoutTableAddAlarmsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->unsignedTinyInteger('strain_alert_setpoint')->nullable();
            $table->unsignedTinyInteger('effort_alert_setpoint')->nullable();
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
            $table->dropColumn('strain_alert_setpoint');
            $table->dropColumn('effort_alert_setpoint');
        });
    }
}
