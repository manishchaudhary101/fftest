<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignKeysUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_User', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('FB_User');
            $table->foreign('modified_by')->references('id')->on('FB_User');
            $table->foreign('status_enum')->references('id')->on('FC_Enum');
            $table->foreign('FC_Country_id')->references('id')->on('FC_Country');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_User', function (Blueprint $table) {
            $table->dropForeign('created_by');
            $table->dropForeign('modified_by');
            $table->dropForeign('status_enum');
            $table->dropForeign('FC_Country_id');

        });
    }
}
