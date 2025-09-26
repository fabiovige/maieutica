<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lgpd_data_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('request_type', ['access', 'correction', 'deletion', 'portability', 'restriction']);
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->json('requested_data')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'request_type']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lgpd_data_requests');
    }
};