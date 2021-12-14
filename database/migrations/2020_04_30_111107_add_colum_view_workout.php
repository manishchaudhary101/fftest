<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumViewWorkout extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {

            $table->integer('arr_normal')->nullable();;
            $table->integer('arr_afib')->nullable();;
            $table->integer('arr_others')->nullable();;
            $table->integer('arr_noise')->nullable();;
            $table->json('arr_details')->nullable();;
            //
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
            $table->dropColumn('arr_normal');
            $table->dropColumn('arr_afib');
            $table->dropColumn('arr_others');
            $table->dropColumn('arr_noise');
            $table->dropColumn('arr_details');
        });
    }
}
