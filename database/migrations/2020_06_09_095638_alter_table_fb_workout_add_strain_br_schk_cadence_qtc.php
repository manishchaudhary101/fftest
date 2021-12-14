<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFbWorkoutAddStrainBrSchkCadenceQtc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->unsignedDecimal('avg_strain')->nullable();
            $table->unsignedDecimal('avg_breathrate')->nullable();
            $table->unsignedDecimal('avg_shock')->nullable();
            $table->unsignedDecimal('avg_cadence')->nullable();
            $table->unsignedDecimal('avg_qtc')->nullable();
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
            $table->dropColumn('avg_strain');
            $table->dropColumn('avg_breathrate');
            $table->dropColumn('avg_shock');
            $table->dropColumn('avg_cadence');
            $table->dropColumn('avg_qtc');
        });
    }
}
