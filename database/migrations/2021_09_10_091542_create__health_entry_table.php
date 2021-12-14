<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHealthEntryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('HealthEntry', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('category_id');
            $table->bigInteger('record_type')->default('1');
            $table->bigInteger('tag_id')->unsigned()->index()->nullable();
            $table->foreign('tag_id')->references('id')->on('Health_Tags_Enum')->onDelete('cascade');
            $table->string('note')->nullable();
            $table->float('quantity')->nullable();
            $table->bigInteger('created_by');
            $table->dateTime('start_time_utc')->nullable();
            $table->bigInteger('time_zone_utc_offset')->nullable();
            $table->bigInteger('tag_entry_time_epoch')->nullable();
            $table->bigInteger('workout_id')->nullable();
            $table->bigInteger('unit_id')->nullable();
            $table->timestamp('created_at');
               
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('HealthEntry');
    }
}
