<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFeLocationdataAddV3Fields1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FE_LocationData', function (Blueprint $table) {
            $table->dropColumn('ctime');
            //$table->dropColumn('speed');
            $table->timestampsTz();
            $table->unsignedMediumInteger('pace')->default(0);
            $table->unsignedMediumInteger('distance')->default(0);
            $table->unsignedBigInteger('timestamp_epoch')->nullable();
            $table->string('timestamp_local')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FE_LocationData', function (Blueprint $table) {
            $table->dropTimestampsTz();
            //$table->double('speed');
            $table->dateTime('ctime');
            $table->dropColumn('pace');
            $table->dropColumn('distance');
            $table->dropColumn('timestamp_epoch');
            $table->dropColumn('timestamp_local');
        });
    }
}
