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
        Schema::create('lgpd_data_requests', function (Blueprint $table) {
            $table->id();

            // Tipo e dados do solicitante
            $table->string('type', 30);
            $table->string('requester_name', 255);
            $table->string('requester_document', 14);
            $table->string('contact_method', 255);

            // Status e datas
            $table->string('status', 20)->default('aberta');
            $table->dateTime('opened_at');
            $table->dateTime('deadline_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            // Resposta e justificativa
            $table->text('response')->nullable();
            $table->text('retention_justification')->nullable();

            // Operadores
            $table->foreignId('assigned_operator_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');

            // Alerta de prazo
            $table->dateTime('alerted_at')->nullable();

            $table->timestamps();

            // Índices
            $table->index('status');
            $table->index('type');
            $table->index('deadline_at');
            $table->index('requester_document');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lgpd_data_requests');
    }
};
