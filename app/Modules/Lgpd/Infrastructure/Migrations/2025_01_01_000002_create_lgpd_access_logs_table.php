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
        Schema::create('lgpd_access_logs', function (Blueprint $table) {
            $table->id();

            // Operador e prontuário
            $table->unsignedBigInteger('operator_id');
            $table->unsignedBigInteger('medical_record_id');

            // Dados do acesso
            $table->string('operation_type', 30);
            $table->string('ip_address', 45);
            $table->string('user_agent', 500);
            $table->dateTime('accessed_at');

            // Apenas created_at — tabela imutável, sem updated_at/deleted_at
            $table->timestamp('created_at')->nullable();

            // Índices
            $table->index('operator_id');
            $table->index('medical_record_id');
            $table->index('accessed_at');
            $table->index('operation_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lgpd_access_logs');
    }
};
