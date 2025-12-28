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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship to patient (Kid or User)
            $table->morphs('patient'); // Creates patient_id and patient_type

            // Session fields
            $table->date('session_date'); // Session date
            $table->text('complaint'); // Patient complaint/demand
            $table->text('objective_technique'); // Objective and technique used
            $table->text('evolution_notes'); // Evolution/progress notes (main field)
            $table->text('referral_closure')->nullable(); // Referral OR closure notes

            // Audit trail
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index('created_by');
            $table->index('session_date');
            $table->index(['patient_id', 'patient_type']); // Morph index
            $table->index(['patient_id', 'patient_type', 'session_date']); // Composite for filters
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medical_records');
    }
};
