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
            $table->string('name')->nullable(); // Adiciona a coluna 'name'
            $table->boolean('is_active')->default(true); // Adiciona a coluna 'is_active' com valor padrão true
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
            $table->dropColumn('name'); // Remove a coluna 'name'
            $table->dropColumn('is_active'); // Remove a coluna 'is_active'
        });
    }
};
