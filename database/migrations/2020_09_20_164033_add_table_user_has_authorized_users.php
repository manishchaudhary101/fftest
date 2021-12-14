<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableUserHasAuthorizedUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FR_User_has_Authorized_Users', function (Blueprint $table) {
            $table->unsignedBigInteger('FB_User_id');
            $table->unsignedBigInteger('FB_Authorized_User_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FR_User_has_Authorized_Users');
    }
}
