<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('kids', function (Blueprint $table) {
            $table->dropColumn('is_adult');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->boolean('is_adult')->default(false)->after('birth_date');
        });

        // Re-popular baseado na idade
        DB::table('kids')
            ->whereNotNull('birth_date')
            ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 13')
            ->update(['is_adult' => true]);
    }
};
