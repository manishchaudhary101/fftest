<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFBUserFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FB_UserFiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestampsTz();
            $table->softDeletesTz(); 
            $table->bigInteger('fb_user_id');
            $table->bigInteger('fb_workout_id')->nullable();
            $table->string('report_name');
            $table->string('report_type')->nullable();
            $table->string('report_notes')->nullable();
            $table->string('report_url');   
        });

        Schema::table('FB_UserFiles',function (Blueprint $table){
            $table->foreign('fb_user_id')->references('id')->on('FB_User');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FB_UserFiles');
    }
}
