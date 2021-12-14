<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFbWorkoutTableAddBinUploadTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table)
        {
            $table->dateTimeTz('binfile_uploaded_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_Workout', function (Blueprint $table)
        {
            $table->dropColumn('binfile_uploaded_at');
        });
    }
}
