<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('object')->nullable();
            $table->integer('object_id')->nullable();
            $table->enum('action', ['insert', 'update', 'remove', 'info']);
            $table->text('description');
            $table->dateTime('creation_date', 0);
            $table->integer('created_by')->nullable();
            $table->dateTime('modification_date', 0)->nullable();
            $table->integer('modified_by')->nullable();
            $table->dateTime('removal_date', 0)->nullable();
            $table->integer('removed_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
