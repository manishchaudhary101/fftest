<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFbWorkoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FB_Workout', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('modified_by')->unsigned()->nullable();
            $table->bigInteger('status_enum')->unsigned()->default(ENUM_STATUS_ACTIVE);
            $table->bigInteger('local_id')->unsigned()->nullable();

            $table->string('title',200)->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('biostrip_macid',36);
            $table->string('firmware_version',50);
            $table->boolean('has_bin_sync')->default(0);
        
            

        });

        Schema::table('FB_Workout',function (Blueprint $table){
            $table->foreign('created_by')->references('id')->on('FB_User');
            $table->foreign('modified_by')->references('id')->on('FB_User');
            $table->foreign('status_enum')->references('id')->on('FC_Enum');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_Workout',function (Blueprint $table){
            $table->dropForeign('created_by');
            $table->dropForeign('modified_by');
            $table->dropForeign('status_enum');

        });
        Schema::dropIfExists('FB_Workout');
    }
}
