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
        Schema::create('lgpd_consent_records', function (Blueprint $table) {
            $table->id();

            // Titular (polimórfico manual — kid ou responsible)
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_type', 50);

            // Dados do consentimento
            $table->string('purpose', 255);
            $table->string('legal_basis', 100);
            $table->unsignedInteger('term_version');
            $table->string('status', 20)->default('ativo');

            // Datas
            $table->dateTime('collected_at');
            $table->dateTime('revoked_at')->nullable();

            // Operadores
            $table->foreignId('collected_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('revoked_by')->nullable()->constrained('users')->onDelete('restrict');

            $table->timestamps();

            // Índices
            $table->index(['subject_id', 'subject_type']);
            $table->index('legal_basis');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lgpd_consent_records');
    }
};
