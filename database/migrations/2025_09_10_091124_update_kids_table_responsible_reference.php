<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kids', function (Blueprint $table) {
            // Remove a foreign key atual que aponta para users
            $table->dropForeign(['responsible_id']);
            
            // Recriar a foreign key apontando para responsibles
            $table->foreign('responsible_id')->references('id')->on('responsibles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kids', function (Blueprint $table) {
            // Remove a foreign key que aponta para responsibles
            $table->dropForeign(['responsible_id']);
            
            // Recriar a foreign key apontando para users (estado anterior)
            $table->foreign('responsible_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
