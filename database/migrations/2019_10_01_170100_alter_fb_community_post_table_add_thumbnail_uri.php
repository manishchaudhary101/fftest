<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFbCommunityPostTableAddThumbnailUri extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Community_post', function (Blueprint $table) {
            $table->text('thumbnail_uri')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_Community_post', function (Blueprint $table) {
            $table->dropColumn('thumbnail_uri');
        });
    }
}
