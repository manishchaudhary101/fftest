<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFbWorkoutTableChangeFieldTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->smallInteger('avg_heart_rate')->nullable()->change();
            $table->smallInteger('avg_strain')->nullable()->change();
            $table->smallInteger('avg_breathrate')->nullable()->change();
            $table->smallInteger('avg_shock')->nullable()->change();
            $table->smallInteger('avg_cadence')->nullable()->change();
            $table->smallInteger('avg_qtc')->nullable()->change();
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
            $table->unsignedSmallInteger('avg_heart_rate')->nullable()->change();
            $table->unsignedDecimal('avg_strain')->nullable()->change();
            $table->unsignedDecimal('avg_breathrate')->nullable()->change();
            $table->unsignedDecimal('avg_shock')->nullable()->change();
            $table->unsignedDecimal('avg_cadence')->nullable()->change();
            $table->unsignedDecimal('avg_qtc')->nullable()->change();
        });
    }
}
