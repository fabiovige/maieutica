<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointment_replacements', function (Blueprint $table) {
            $table->uuid('operation_id')->nullable()->after('appointment_id')->unique();
        });
    }

    public function down()
    {
        Schema::table('appointment_replacements', function (Blueprint $table) {
            $table->dropUnique(['operation_id']);
            $table->dropColumn('operation_id');
        });
    }
};
