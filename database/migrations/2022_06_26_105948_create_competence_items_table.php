<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetenceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competence_items', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->nullable();
            $table->unsignedBigInteger('competence_id')->nullable();
            $table->integer('code');
            $table->longText('description');
            $table->longText('description_detail');
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->default(1);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

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
        Schema::dropIfExists('competence_items');
    }
}
