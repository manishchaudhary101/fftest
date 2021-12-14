<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPdfToFbWorkoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FB_Workout', function (Blueprint $table) {
            $table->smallInteger('is_ecg_pdf_generated')->default(0);
            $table->text('ecg_pdf_url')->nullable();
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
            $table->dropColumn('is_ecg_pdf_generated');
            $table->dropColumn('ecg_pdf_url');
        });
    }
}
