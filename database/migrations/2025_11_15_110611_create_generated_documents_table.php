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
            $table->foreignId('document_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('kid_id')->constrained()->onDelete('cascade');
            $table->foreignId('checklist_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quem gerou o documento
            $table->string('file_path'); // Caminho do PDF gerado
            $table->json('data_used'); // Dados usados na geração (auditoria)
            $table->timestamp('generated_at');
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
        Schema::dropIfExists('generated_documents');
    }
};
