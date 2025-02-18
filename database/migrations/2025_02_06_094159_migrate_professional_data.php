<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateProfessionalData extends Migration
{
    public function up()
    {
        // Migrar dados existentes para a nova tabela
        $kids = DB::table('kids')->whereNotNull('profession_id')->get();

        foreach ($kids as $kid) {
            DB::table('kid_professional')->insert([
                'kid_id' => $kid->id,
                'user_id' => $kid->profession_id,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('kid_professional')->truncate();
    }
}
