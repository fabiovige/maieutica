<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->boolean('is_adult')->default(false)->after('birth_date');
        });

        // Marcar pacientes com 18+ anos como adultos
        DB::table('kids')
            ->whereNull('deleted_at')
            ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 18')
            ->update(['is_adult' => true]);
    }

    public function down()
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->dropColumn('is_adult');
        });
    }
};
