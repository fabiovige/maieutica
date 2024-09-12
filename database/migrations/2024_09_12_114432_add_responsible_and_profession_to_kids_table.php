<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponsibleAndProfessionToKidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kids', function (Blueprint $table) {
            // Adiciona a coluna para o responsável (pai/mãe)
            $table->unsignedBigInteger('responsible_id')->nullable(); // Referencia o responsável (pai/mãe)
            $table->foreign('responsible_id')->references('id')->on('users')->onDelete('set null'); // Define a FK para a tabela users

            // Adiciona a coluna para o profissional (professor/mentor)
            $table->unsignedBigInteger('profession_id')->nullable(); // Referencia o profissional
            $table->foreign('profession_id')->references('id')->on('users')->onDelete('set null'); // Define a FK para a tabela users
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
            // Remove as FKs e colunas ao reverter a migração
            $table->dropForeign(['responsible_id']);
            $table->dropColumn('responsible_id');

            $table->dropForeign(['profession_id']);
            $table->dropColumn('profession_id');
        });
    }
}
