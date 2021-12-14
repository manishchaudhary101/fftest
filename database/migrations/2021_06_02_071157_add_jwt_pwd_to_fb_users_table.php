<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJwtPwdToFbUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_User', function (Blueprint $table) {
            $table->string('jwt_token',256)->nullable();
            $table->string('jwt_password',56)->nullable();
        
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
            $table->dropColumn('jwt_token');
            $table->dropColumn('jwt_password');
       });
    }
}
