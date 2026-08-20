<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Chave de correlacao com o Google Calendar (fonte do evento)
            $table->string('google_event_id')->unique();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();

            // Dados informados pelo paciente durante a conversa no WhatsApp
            $table->string('patient_name')->nullable();
            $table->string('patient_email')->nullable();
            $table->string('patient_phone')->nullable();
            $table->text('reason')->nullable();

            // Texto livre vindo da descricao do evento - apenas referencia para
            // a conferencia humana; o vinculo real e o professional_id abaixo.
            $table->string('specialty_raw')->nullable();
            $table->string('professional_raw')->nullable();

            // Preenchido na confirmacao, quando o texto livre vira vinculo real
            $table->foreignId('professional_id')->nullable()->constrained('professionals')->onDelete('set null');

            // Ver App\Models\Appointment::SITUATION
            $table->char('situation', 1)->default('p');

            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('confirmed_at')->nullable();

            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index('starts_at');
            $table->index('situation');
            $table->index('professional_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
