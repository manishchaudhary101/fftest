<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAlertRenameFBWorkout extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->renameColumn('heart_lower_limit_alert', 'double_buzz_param');
            $table->renameColumn('heart_upper_limit_alert', 'single_buzz_param');
            $table->renameColumn('effort_lower_limit_alert', 'double_buzz_limit');
            $table->renameColumn('effort_upper_limit_alert', 'single_buzz_limit');
            $table->renameColumn('strain_lower_limit_alert','double_buzz_val');
            $table->renameColumn('strain_upper_limit_alert','single_buzz_val');

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
            $table->dropColumn('double_buzz_param','heart_lower_limit_alert');
            $table->dropColumn('single_buzz_param','heart_upper_limit_alert');
            $table->dropColumn('double_buzz_limit','effort_lower_limit_alert');
            $table->dropColumn( 'single_buzz_limit','effort_upper_limit_alert');
            $table->dropColumn('double_buzz_val','strain_lower_limit_alert');
            $table->dropColumn('single_buzz_val','strain_upper_limit_alert');

        });
    }
}
