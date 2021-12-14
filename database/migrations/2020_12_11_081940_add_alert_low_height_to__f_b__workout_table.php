<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddALERTLOWHEIGHTToFBWorkoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->smallInteger('heart_lower_limit_alert')->default(null);
            $table->smallInteger('heart_upper_limit_alert')->default(null);
            $table->smallInteger('effort_lower_limit_alert')->default(null);
            $table->smallInteger('effort_upper_limit_alert')->default(null);
            $table->smallInteger('strain_lower_limit_alert')->default(null);
            $table->smallInteger('strain_upper_limit_alert')->default(null);
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
            $table->dropColumn('heart_lower_limit_alert');
            $table->dropColumn('heart_upper_limit_alert');
            $table->dropColumn('effort_lower_limit_alert');
            $table->dropColumn('effort_upper_limit_alert');
            $table->dropColumn('strain_lower_limit_alert');
            $table->dropColumn('strain_upper_limit_alert');

        });
    }
}
