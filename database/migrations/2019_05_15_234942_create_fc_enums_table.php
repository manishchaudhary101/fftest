<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFcEnumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FC_Enum', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('group_id')->nullable();
            $table->string('name');
            $table->string('value_text')->nullable();
            $table->decimal('value_int',11,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FC_Enum');
    }
}
