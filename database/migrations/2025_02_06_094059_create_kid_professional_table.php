<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKidProfessionalTable extends Migration
{
    public function up()
    {
        Schema::create('kid_professional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kid_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false); // Para identificar o profissional principal
            $table->timestamps();

            // Evitar duplicatas
            $table->unique(['kid_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kid_professional');
    }
}
