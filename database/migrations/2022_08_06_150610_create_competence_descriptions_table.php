<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetenceDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competence_descriptions', function (Blueprint $table) {
            $table->id();
            $table->enum('level',[1,2,3,4]);
            $table->unsignedBigInteger('competence_id');
            $table->integer('code');
            $table->text('description');
            $table->text('description_detail');
            $table->timestamps();

            $table->foreign('competence_id')->references('id')->on('competences')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competence_descriptions');
    }
}
