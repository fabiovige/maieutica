<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('specialties')) {
            Schema::create('specialties', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->foreignId('deleted_by')->nullable()->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Adicionar colunas se não existirem
        Schema::table('specialties', function (Blueprint $table) {
            if (! Schema::hasColumn('specialties', 'description')) {
                $table->text('description')->nullable();
            }
            if (! Schema::hasColumn('specialties', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users');
            }
            if (! Schema::hasColumn('specialties', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users');
            }
            if (! Schema::hasColumn('specialties', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('users');
            }
            if (! Schema::hasColumn('specialties', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('specialties');
    }
};
