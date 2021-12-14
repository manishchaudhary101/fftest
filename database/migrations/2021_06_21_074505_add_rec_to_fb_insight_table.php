<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecToFbInsightTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Insight', function (Blueprint $table) {
            $table->bigInteger('rec_workout_duration')->nullable();
            $table->bigInteger('rec_activity_type')->nullable();
            $table->bigInteger('rec_daily_TL')->nullable();
            $table->string('rec_effort')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_Insight', function (Blueprint $table) {
            $table->dropColumn('rec_workout_duration');
            $table->dropColumn('rec_activity_type');
            $table->dropColumn('rec_daily_TL');
            $table->dropColumn('rec_effort');
      
        });
    }
}
