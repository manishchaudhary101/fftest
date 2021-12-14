<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHealthTagsEnumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Health_Tags_Enum', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->bigInteger('tag_type_id')->unsigned()->index()->nullable();
            $table->bigInteger('unit_id')->unsigned()->index()->nullable();
            $table->bigInteger('category_id')->unsigned()->index()->nullable();
            $table->foreign('tag_type_id')->references('id')->on('Health_Tag_Type_Enum')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('Health_Units_Enum')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('Health_Category_Enum')->onDelete('cascade');
            $table->bigInteger('priority')->nullable();
            $table->boolean('status')->nullable();
            $table->timestamp('modified_date')->nullable();
                
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Health_Tags_Enum');
    }
}
