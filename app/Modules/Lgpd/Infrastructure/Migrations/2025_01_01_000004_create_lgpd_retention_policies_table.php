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
        Schema::create('lgpd_retention_policies', function (Blueprint $table) {
            $table->id();

            // Categoria com constraint UNIQUE
            $table->string('category', 50)->unique();

            // Configuração de retenção
            $table->unsignedInteger('retention_days');
            $table->string('expiration_action', 30);
            $table->unsignedInteger('legal_minimum_days');
            $table->string('legal_reference', 255)->nullable();

            // Operadores
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');

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
        Schema::dropIfExists('lgpd_retention_policies');
    }
};
