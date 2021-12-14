<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFcPolicyTableAddExtraFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FC_Policy', function (Blueprint $table) {
            $table->longText('description')->nullable();
            $table->boolean('is_displayed')->default(true);
            $table->boolean('is_mandatory')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FC_Policy', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('is_displayed');
            $table->dropColumn('is_mandatory');
        });
    }
}
