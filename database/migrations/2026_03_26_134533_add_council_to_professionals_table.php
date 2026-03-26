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
        Schema::table('professionals', function (Blueprint $table) {
            $table->string('council', 20)->nullable()->after('registration_number');
        });
    }

    public function down()
    {
        Schema::table('professionals', function (Blueprint $table) {
            $table->dropColumn('council');
        });
    }
};
