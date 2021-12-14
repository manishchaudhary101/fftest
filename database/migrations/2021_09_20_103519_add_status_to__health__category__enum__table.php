<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToHealthCategoryEnumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Health_Category_Enum', function (Blueprint $table) {
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
        Schema::table('Health_Category_Enum', function (Blueprint $table) {
            //
        });
    }
}
