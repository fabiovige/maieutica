<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('professionals')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();

            // Ultima barreira para duas confirmacoes no mesmo inicio exato.
            // Sobreposicoes parciais tambem sao verificadas pelo controller.
            $table->unique(['professional_id', 'starts_at'], 'appointment_slots_professional_start_unique');
            $table->index(['professional_id', 'starts_at', 'ends_at'], 'appointment_slots_overlap_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointment_slots');
    }
};
