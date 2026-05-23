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
        Schema::create('lgpd_consent_legal_basis_history', function (Blueprint $table) {
            $table->id();

            // Referência ao consentimento
            $table->foreignId('consent_record_id')
                ->constrained('lgpd_consent_records')
                ->onDelete('cascade');

            // Dados da alteração
            $table->string('previous_legal_basis', 100);
            $table->string('new_legal_basis', 100);
            $table->text('justification');

            // Operador e data
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->dateTime('changed_at');

            // Apenas created_at — tabela imutável de histórico
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lgpd_consent_legal_basis_history');
    }
};
