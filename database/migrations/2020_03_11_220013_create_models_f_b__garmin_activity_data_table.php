<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModelsFBGarminActivityDataTable extends Migration
{
    /**
     * 'startTimeInSeconds' => 1583560484,
    'airTemperatureCelcius' => 30.0,
    'heartRate' => 137,
    'speedMetersPerSecond' => 3.806999921798706,
    'stepsPerMinute' => 81.0,
    'totalDistanceInMeters' => 3.819999933242798,
    'timerDurationInSeconds' => 0,
    'clockDurationInSeconds' => 0,
    'movingDurationInSeconds' => 0,
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FB_GarminActivityData', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('FB_GarminActivitySummary_id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('summaryId');
            $table->unsignedInteger('startTimeInSeconds')->default(0);
            $table->unsignedDecimal('latitudeInDegree',10,7)->default(0);
            $table->unsignedDecimal('longitudeInDegree',10,7)->default(0);
            $table->unsignedDecimal('elevationInMeters',7,2)->default(0);
            $table->unsignedDecimal('airTemperatureCelcius',5,2)->default(0);
            $table->unsignedMediumInteger('heartRate')->default(0);
            $table->unsignedDecimal('speedMetersPerSecond',7,2)->default(0);
            $table->unsignedDecimal('stepsPerMinute',7,2)->default(0);
            $table->unsignedDecimal('totalDistanceInMeters',7,2)->default(0);
            $table->unsignedInteger('timerDurationInSeconds')->default(0);
            $table->unsignedInteger('clockDurationInSeconds')->default(0);
            $table->unsignedInteger('movingDurationInSeconds')->default(0);
            $table->unsignedDecimal('powerInWatts',7,2)->default(0);
            $table->unsignedDecimal('bikeCadenceInRPM',7,2)->default(0);
            $table->unsignedDecimal('swimCadenceInStrokesPerMinute',7,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FB_GarminActivityData');
    }
}
