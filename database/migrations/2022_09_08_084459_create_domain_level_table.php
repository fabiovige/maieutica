<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainLevelTable extends Migration
{
    public function up()
    {
        Schema::create('domain_level', function (Blueprint $table) {
            $table->unsignedBigInteger('domain_id');
            $table->unsignedBigInteger('level_id');

            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
            $table->foreign('level_id')->references('id')->on('levels')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('domain_level');
    }
}
