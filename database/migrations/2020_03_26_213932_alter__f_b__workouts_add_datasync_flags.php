<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFBWorkoutsAddDatasyncFlags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->unsignedSmallInteger('updated_source')->nullable();
            $table->dateTimeTz('last_synced_timestamp')->nullable();
            $table->boolean('is_synced_with_app')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->dropColumn('updated_source');
            $table->dropColumn('last_synced_timestamp');
            $table->dropColumn('is_synced_with_app');
        });
    }
}
