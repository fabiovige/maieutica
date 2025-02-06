<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenderAndEthnicityToKids extends Migration
{
    public function up()
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->enum('gender', ['M', 'F'])->nullable()->after('name');
            $table->enum('ethnicity', [
                'branco',
                'pardo',
                'negro',
                'indigena',
                'amarelo',
                'multiracial',
                'nao_declarado',
                'outro'
            ])->nullable()->after('gender');
        });
    }

    public function down()
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->dropColumn(['gender', 'ethnicity']);
        });
    }
}
