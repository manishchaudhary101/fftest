<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFcCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FC_City', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('FC_State_id')->unsigned();
            $table->bigInteger('status_enum')->unsigned()->nullable();
            $table->string('name',150);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FC_City');
    }
}
