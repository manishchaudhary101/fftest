<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableGarminActivitySummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
/*
        'userId' => '04ff4b3c-6fd2-484d-a2b0-88a97703c5f1',
      'userAccessToken' => 'c61475c8-86aa-46e4-8c50-15c15305385e',
      'summaryId' => '4605479132',
      'durationInSeconds' => 3025,
      'startTimeInSeconds' => 1583022075,
      'startTimeOffsetInSeconds' => 19800,
      'activityType' => 'RUNNING',
      'averageHeartRateInBeatsPerMinute' => 165, //int
      'averageRunCadenceInStepsPerMinute' => 164.57812, //2 decimals
      'averageSpeedInMetersPerSecond' => 3.227, //2 decimals
      'averagePaceInMinutesPerKilometer' => 5.164756, //2 decimals
      'activeKilocalories' => 668, //int
      'deviceName' => 'fenix5xAPAC',
      'distanceInMeters' => 9765.68, //2 decimals
      'maxHeartRateInBeatsPerMinute' => 188, //int
      'maxPaceInMinutesPerKilometer' => 3.9607096, //2 decimals
      'maxRunCadenceInStepsPerMinute' => 173.0, //2 decimals
      'maxSpeedInMetersPerSecond' => 4.208, //2 decimals
      'startingLatitudeInDegree' => 12.849730895832181, // 7 decimals
      'startingLongitudeInDegree' => 77.66219484619796, // 7 decimals
      'steps' => 8288,
      'totalElevationGainInMeters' => 64.0,
      'totalElevationLossInMeters' => 70.0,
*/
        Schema::create('FB_GarminActivitySummary', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('userId');
            $table->string('userAccessToken');
            $table->string('summaryId');
            $table->unsignedMediumInteger('durationInSeconds')->default(0);
            $table->unsignedDecimal('averageBikeCadenceInRoundsPerMinute',7,2)->default(0);
            $table->unsignedInteger('startTimeInSeconds')->default(0);
            $table->unsignedInteger('startTimeOffsetInSeconds')->default(0);
            $table->string('activityType')->nullable();
            $table->unsignedMediumInteger('averageHeartRateInBeatsPerMinute')->default(0);
            $table->unsignedDecimal('averageRunCadenceInStepsPerMinute',7,2)->default(0);
            $table->unsignedDecimal('averageSpeedInMetersPerSecond',7,2)->default(0);
            $table->unsignedDecimal('averageSwimCadenceInStrokesPerMinute',7,2)->default(0);
            $table->unsignedDecimal('averagePaceInMinutesPerKilometer',7,2)->default(0);
            $table->unsignedMediumInteger('activeKilocalories')->default(0);
            $table->string('deviceName')->nullable();
            $table->unsignedDecimal('distanceInMeters',7,2)->default(0);
            $table->unsignedDecimal('maxBikeCadenceInRoundsPerMinute',7,2)->default(0);
            $table->unsignedDecimal('maxHeartRateInBeatsPerMinute',7,2)->default(0);
            $table->unsignedDecimal('maxPaceInMinutesPerKilometer',7,2)->default(0);
            $table->unsignedDecimal('maxRunCadenceInStepsPerMinute',7,2)->default(0);
            $table->unsignedDecimal('maxSpeedInMetersPerSecond',7,2)->default(0);
            $table->unsignedMediumInteger('numberOfActiveLengths')->default(0);
            $table->unsignedDecimal('startingLatitudeInDegree',10,7)->default(0);
            $table->unsignedDecimal('startingLongitudeInDegree',10,7)->default(0);
            $table->unsignedBigInteger('steps')->default(0);
            $table->unsignedDecimal('totalElevationGainInMeters',5,2)->default(0);
            $table->unsignedDecimal('totalElevationLossInMeters',5,2)->default(0);
            $table->boolean('isParent')->nullable();
            $table->string('parentSummaryId')->nullable();
            $table->boolean('manual')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FB_GarminActivitySummary');
    }
}
