<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Kid;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Popular o campo months para registros existentes
        Kid::whereNull('months')->chunk(100, function ($kids) {
            foreach ($kids as $kid) {
                if ($kid->birth_date) {
                    try {
                        // Calcular months baseado na birth_date
                        $birthDate = Carbon::parse($kid->getRawOriginal('birth_date'));
                        $months = $birthDate->diffInMonths(Carbon::now());
                        
                        $kid->update(['months' => $months]);
                    } catch (Exception $e) {
                        // Log error mas não para o processo
                        \Log::warning("Erro ao calcular months para kid {$kid->id}: " . $e->getMessage());
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Opcional: limpar o campo months se necessário
        Kid::query()->update(['months' => null]);
    }
};