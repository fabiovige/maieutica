<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_registers', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('checklist_id');
            $table->unsignedBigInteger('competence_description_id');
            $table->integer('note');
            $table->timestamps();

            $table->foreign('checklist_id')
                ->references('id')
                ->on('checklists')
                ->onDelete('cascade');

            $table->foreign('competence_description_id')
                ->references('id')
                ->on('competence_descriptions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checklist_registers');
    }
}
