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
        Schema::table('competences', function (Blueprint $table) {
            $table->integer('percentil_25')->nullable()->after('description_detail');
            $table->integer('percentil_50')->nullable()->after('percentil_25');
            $table->integer('percentil_75')->nullable()->after('percentil_50');
            $table->integer('percentil_90')->nullable()->after('percentil_75');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('competences', function (Blueprint $table) {
            $table->dropColumn(['percentil_25', 'percentil_50', 'percentil_75', 'percentil_90']);
        });
    }
};
