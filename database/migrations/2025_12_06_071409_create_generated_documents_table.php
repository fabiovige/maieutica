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
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();

            // Tipo de documento (1-6 = modelo 1-6)
            $table->tinyInteger('model_type')
                ->comment('1=Declaração, 2=Decl.Simples, 3=Laudo, 4=Parecer, 5=Multip, 6=Relatório');

            // Relacionamento polimórfico - pode ser Kid ou User
            $table->morphs('documentable'); // documentable_id + documentable_type

            // Profissional que assina o documento
            $table->foreignId('professional_id')
                ->nullable()
                ->constrained('professionals')
                ->nullOnDelete();

            // Usuário que gerou o documento
            $table->foreignId('generated_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Armazenamento do HTML renderizado (núcleo da solução)
            $table->longText('html_content')
                ->comment('HTML renderizado do Blade para regeneração de PDF');

            // Dados do formulário original (JSON) para auditoria
            $table->json('form_data')
                ->nullable()
                ->comment('Dados originais do request para referência');

            // Metadata adicional (JSON)
            $table->json('metadata')
                ->nullable()
                ->comment('IP, user_agent, document_title, etc.');

            // Timestamps de auditoria
            $table->timestamp('generated_at')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->index(['documentable_id', 'documentable_type'], 'idx_documentable');
            $table->index('model_type');
            $table->index('professional_id');
            $table->index('generated_by');
            $table->index('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generated_documents');
    }
};
