
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAlterEnumlevelInFbUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_User', function (Blueprint $table) {
            $table->bigInteger('userlevel_enum')->default(10)->change();
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
            $table->bigInteger('userlevel_enum')->default(10)->change();
        });
    }
}
