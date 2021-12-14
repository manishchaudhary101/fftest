<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFBUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FB_User', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->softDeletes();
            $table->timestamps();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('modified_by')->unsigned()->nullable();
            $table->bigInteger('status_enum')->unsigned()->nullable();
            $table->string('name',150);
            $table->string('email',150)->unique();
            $table->string('password',255);
            $table->bigInteger('FC_Country_id')->unsigned();
            $table->bigInteger('gender_enum')->unsigned()->nullable();
            $table->decimal('height',6,2)->unsigned()->nullable();
            $table->decimal('weight',6,2)->unsigned()->nullable();
            $table->dateTime('dob')->nullable();
            $table->string('api_token',255)->nullable();
            $table->dateTime('api_token_expiry')->nullable();
            $table->bigInteger('userlevel_enum')->unsigned()->nullable();
            $table->string('mobile_udid')->nullable();
            $table->bigInteger('mobile_platform_type_enum')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FB_User');
    }
}
