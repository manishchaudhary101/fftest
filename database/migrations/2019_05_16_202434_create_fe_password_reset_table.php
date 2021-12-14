<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFePasswordResetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FE_PasswordReset', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('FB_User_id')->unsigned()->nullable();

            $table->string('reset_token',100)->index();
            $table->dateTime('token_expiry');

        });

        Schema::table('FE_PasswordReset', function (Blueprint $table) {
            $table->foreign('FB_User_id')->on('FB_User')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FE_PasswordReset', function (Blueprint $table) {
            $table->dropForeign('FB_User_id');

        });

        Schema::dropIfExists('FE_PasswordReset');
    }
}
