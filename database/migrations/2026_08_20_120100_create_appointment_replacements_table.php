<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointment_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->char('action', 1);
            $table->string('google_event_id');

            $table->string('old_patient_name')->nullable();
            $table->string('old_patient_email')->nullable();
            $table->string('old_patient_phone')->nullable();
            $table->text('old_reason')->nullable();
            $table->foreignId('old_professional_id')->nullable()
                ->constrained('professionals')->nullOnDelete();

            $table->string('new_patient_name')->nullable();
            $table->string('new_patient_email')->nullable();
            $table->string('new_patient_phone')->nullable();
            $table->text('new_reason')->nullable();
            $table->foreignId('new_professional_id')->nullable()
                ->constrained('professionals')->nullOnDelete();

            $table->text('cancellation_reason')->nullable();
            $table->foreignId('performed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->dateTime('occurred_at');
            $table->timestamps();

            $table->index(['appointment_id', 'occurred_at']);
            $table->index('action');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointment_replacements');
    }
};
