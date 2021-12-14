<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFBNoteDocTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FB_Note_Doc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->text('note');
            $table->bigInteger('FB_Workout_id')->nullable();
            $table->bigInteger('FB_User_id');
            $table->bigInteger('FB_Notes_Category')->nullable();
            $table->bigInteger('FB_Doctor_UserID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FB_Note_Doc');
    }
}
