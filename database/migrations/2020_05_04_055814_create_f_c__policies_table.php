<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFCPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FC_Policy', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestampsTz();
            $table->integer('policy_id');
            $table->string('name');
            $table->longText('content')->nullable();
            $table->text('url')->nullable();
            $table->decimal('version')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('f_c__policies');
    }
}
