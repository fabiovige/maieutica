<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->nullable();
            $table->text('bio')->nullable();
            $table->foreignId('specialty_id')->constrained()->onDelete('restrict');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabela pivot entre users e professionals
        Schema::create('user_professional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('professional_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Garantir que um usuário só tenha um registro profissional
            $table->unique(['user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_professional');
        Schema::dropIfExists('professionals');
    }
};
