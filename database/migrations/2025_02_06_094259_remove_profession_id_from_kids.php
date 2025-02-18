<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveProfessionIdFromKids extends Migration
{
    public function up()
    {
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
    }
}
