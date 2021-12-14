<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourcePlatformToFBNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Note', function (Blueprint $table) {
            $table->smallInteger('source_platform')->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_Note', function (Blueprint $table) {
            $table->smallInteger('source_platform')->default(null)->nullable();
        });
    }
}
