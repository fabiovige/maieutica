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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action', 50);
            $table->string('resource', 100);
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->json('data_before')->nullable();
            $table->json('data_after')->nullable();
            $table->text('context')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['resource', 'resource_id']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
