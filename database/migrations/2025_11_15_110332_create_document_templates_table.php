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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome do template (ex: "Declaração de Atendimento")
            $table->string('type'); // Tipo: declaracao, laudo, termo
            $table->longText('html_content'); // HTML do template com placeholders
            $table->text('description')->nullable(); // Descrição opcional
            $table->json('available_placeholders'); // JSON com lista de placeholders disponíveis
            $table->string('version')->default('1.0'); // Controle de versão
            $table->boolean('is_active')->default(true); // Template ativo/inativo
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_templates');
    }
};
