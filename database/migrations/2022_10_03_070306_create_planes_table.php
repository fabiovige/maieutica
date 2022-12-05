<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanesTable extends Migration
{

    public function up()
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kid_id')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->softDeletes();

            $table->foreign('kid_id')->references('id')->on('kids')->onDelete('SET NULL');
        });
    }

    public function down()
    {
        Schema::dropIfExists('planes');
    }
}
