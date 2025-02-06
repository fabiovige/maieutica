<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Primeiro criar nova tabela
        Schema::create('kid_professional_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kid_id')->constrained()->onDelete('cascade');
            $table->foreignId('professional_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Evitar duplicatas
            $table->unique(['kid_id', 'professional_id']);
        });

        // Migrar dados da tabela antiga para a nova
        if (Schema::hasTable('kid_professional')) {
            DB::statement("
                INSERT INTO kid_professional_new (kid_id, professional_id, is_primary, created_at, updated_at)
                SELECT kp.kid_id, p.id, kp.is_primary, kp.created_at, kp.updated_at
                FROM kid_professional kp
                JOIN user_professional up ON up.user_id = kp.user_id
                JOIN professionals p ON p.id = up.professional_id
            ");

            // Remover tabela antiga
            Schema::dropIfExists('kid_professional');
        }

        // Renomear nova tabela
        Schema::rename('kid_professional_new', 'kid_professional');
    }

    public function down()
    {
        Schema::dropIfExists('kid_professional');
    }
};
