<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFbWorkoutAddTotalDistanceAvgPace extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_distance')->default(0);
            $table->unsignedDecimal('avg_pace')->default(0);
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
            $table->dropColumn('total_distance');
            $table->dropColumn('avg_pace');
        });
    }
}
