<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->string('name', 1000)->change();
        });

        Schema::table('responsibles', function (Blueprint $table) {
            $table->string('name', 1000)->change();
            $table->string('email', 1000)->change();
            $table->string('cpf', 1000)->nullable()->change();
            $table->string('cell', 1000)->nullable()->change();
            $table->string('cep', 1000)->nullable()->change();
            $table->string('logradouro', 1000)->nullable()->change();
            $table->string('numero', 1000)->nullable()->change();
            $table->string('complemento', 1000)->nullable()->change();
            $table->string('bairro', 1000)->nullable()->change();
            $table->string('cidade', 1000)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 1000)->change();
            $table->string('phone', 1000)->nullable()->change();
            $table->string('postal_code', 1000)->nullable()->change();
            $table->string('street', 1000)->nullable()->change();
            $table->string('number', 1000)->nullable()->change();
            $table->string('complement', 1000)->nullable()->change();
            $table->string('neighborhood', 1000)->nullable()->change();
            $table->string('city', 1000)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->string('name', 255)->change();
        });

        Schema::table('responsibles', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('email', 255)->change();
            $table->string('cpf', 255)->nullable()->change();
            $table->string('cell', 255)->nullable()->change();
            $table->string('cep', 255)->nullable()->change();
            $table->string('logradouro', 255)->nullable()->change();
            $table->string('numero', 255)->nullable()->change();
            $table->string('complemento', 255)->nullable()->change();
            $table->string('bairro', 255)->nullable()->change();
            $table->string('cidade', 255)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('phone', 255)->nullable()->change();
            $table->string('postal_code', 255)->nullable()->change();
            $table->string('street', 255)->nullable()->change();
            $table->string('number', 255)->nullable()->change();
            $table->string('complement', 255)->nullable()->change();
            $table->string('neighborhood', 255)->nullable()->change();
            $table->string('city', 255)->nullable()->change();
        });
    }
};
