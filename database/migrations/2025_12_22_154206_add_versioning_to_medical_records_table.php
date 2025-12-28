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
        Schema::table('medical_records', function (Blueprint $table) {
            // Versionamento
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->integer('version')->default(1)->after('parent_id');
            $table->boolean('is_current_version')->default(true)->after('version');
            
            // HTML salvo (como gerador de documentos)
            $table->longText('html_content')->nullable()->after('referral_closure');
            
            // Foreign key para parent (versão original)
            $table->foreign('parent_id')->references('id')->on('medical_records')->onDelete('cascade');
            
            // Indexes para performance
            $table->index('parent_id');
            $table->index('is_current_version');
            $table->index(['parent_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['is_current_version']);
            $table->dropIndex(['parent_id', 'version']);
            
            $table->dropColumn(['parent_id', 'version', 'is_current_version', 'html_content']);
        });
    }
};
