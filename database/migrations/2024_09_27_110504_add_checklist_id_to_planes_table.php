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
        Schema::table('planes', function (Blueprint $table) {
            $table->foreignId('checklist_id')->constrained()->onDelete('cascade'); // Relacionamento Plane -> Checklist
            //$table->dropColumn('kid_id');  // Remover a coluna kid_id, pois o Plane está agora ligado ao Checklist
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('planes', function (Blueprint $table) {
            //$table->foreignId('kid_id')->constrained()->onDelete('cascade');
            $table->dropForeign(['checklist_id']);
            $table->dropColumn('checklist_id');
        });
    }
};
