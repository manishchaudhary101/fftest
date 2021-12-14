<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFrCommunityPostsHasTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FR_Community_posts_has_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('FB_Community_Posts_id');
            $table->unsignedBigInteger('FB_Community_Tags_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FR_Community_posts_has_tags');
    }
}
