<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('canceled_by')->nullable()->after('confirmed_at')
                ->constrained('users')->onDelete('set null');
            $table->dateTime('canceled_at')->nullable()->after('canceled_by');
            $table->text('cancellation_reason')->nullable()->after('canceled_at');
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['canceled_by']);
            $table->dropColumn(['canceled_by', 'canceled_at', 'cancellation_reason']);
        });
    }
};
