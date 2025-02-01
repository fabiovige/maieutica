<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateKidProfessionalTable extends Migration
{
    public function up()
    {
        Schema::create('kid_professional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kid_id')->constrained('kids')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Garante que um profissional só pode ser associado uma vez a cada kid
            $table->unique(['kid_id', 'user_id']);
        });

        // Migrar dados existentes
        DB::statement('
            INSERT INTO kid_professional (kid_id, user_id, is_primary, created_at, updated_at)
            SELECT id, profession_id, true, NOW(), NOW()
            FROM kids
            WHERE profession_id IS NOT NULL
        ');

        // Remover a coluna antiga após migrar os dados
        Schema::table('kids', function (Blueprint $table) {
            $table->dropForeign(['profession_id']);
            $table->dropColumn('profession_id');
        });
    }

    public function down()
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->foreignId('profession_id')->nullable()->constrained('users');
        });

        // Restaurar dados
        DB::statement('
            UPDATE kids k
            JOIN kid_professional kp ON k.id = kp.kid_id
            WHERE kp.is_primary = true
            SET k.profession_id = kp.user_id
        ');

        Schema::dropIfExists('kid_professional');
    }
}
