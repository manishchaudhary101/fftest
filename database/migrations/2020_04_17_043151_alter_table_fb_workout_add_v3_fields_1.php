<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFbWorkoutAddV3Fields1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->smallInteger('source_platform')->default(null)->nullable();
            $table->string('app_version')->nullable();
            $table->string('phone_os_version')->nullable();
            $table->unsignedSmallInteger('avg_heart_rate')->default(0);
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
            $table->dropColumn('source_platform');
            $table->dropColumn('app_version');
            $table->dropColumn('phone_os_version');
            $table->dropColumn('avg_heart_rate');
        });
    }
}
