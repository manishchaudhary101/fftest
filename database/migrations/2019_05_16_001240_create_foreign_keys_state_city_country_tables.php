<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignKeysStateCityCountryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FC_Country', function (Blueprint $table) {
            $table->foreign('status_enum')->references('id')->on('FC_Enum');

        });
        Schema::table('FC_State', function (Blueprint $table) {
            $table->foreign('status_enum')->references('id')->on('FC_Enum');
            $table->foreign('FC_Country_id')->references('id')->on('FC_Country');

        });
        Schema::table('FC_City', function (Blueprint $table) {
            $table->foreign('status_enum')->references('id')->on('FC_Enum');
            $table->foreign('FC_State_id')->references('id')->on('FC_State');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FC_Country', function (Blueprint $table) {
            $table->dropForeign('status_enum');

        });
        Schema::table('FC_State', function (Blueprint $table) {
            $table->dropForeign('status_enum');
            $table->dropForeign('FC_Country_id');

        });
        Schema::table('FC_City', function (Blueprint $table) {
            $table->dropForeign('status_enum');
            $table->dropForeign('FC_State_id');

        });
    }
}
