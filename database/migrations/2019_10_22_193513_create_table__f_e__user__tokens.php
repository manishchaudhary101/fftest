<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFEUserTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_FE_User_Token', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('token',256);
            $table->unsignedBigInteger('FB_User_id');
            $table->dateTime('expiry');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_FE_User_Token');
    }
}
