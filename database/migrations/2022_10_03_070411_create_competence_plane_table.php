<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetencePlaneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competence_plane', function (Blueprint $table) {
            $table->unsignedBigInteger('plane_id');
            $table->unsignedBigInteger('competence_id');

            $table->foreign('plane_id')->references('id')->on('planes')->onDelete('cascade');
            $table->foreign('competence_id')->references('id')->on('competences')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competence_plane');
    }
}
