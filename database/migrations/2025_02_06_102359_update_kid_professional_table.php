<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Primeiro dropar a tabela antiga se existir
        Schema::dropIfExists('kid_professional');

        // Criar a nova tabela
        Schema::create('kid_professional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kid_id')->constrained()->onDelete('cascade');
            $table->foreignId('professional_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Evitar duplicatas
            $table->unique(['kid_id', 'professional_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kid_professional');
    }
};
