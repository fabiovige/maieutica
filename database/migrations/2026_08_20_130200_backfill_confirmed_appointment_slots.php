<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('appointments')
            ->where('situation', 'c')
            ->whereNotNull('professional_id')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunkById(100, function ($appointments) {
                foreach ($appointments as $appointment) {
                    DB::table('appointment_slots')->insertOrIgnore([
                        'appointment_id' => $appointment->id,
                        'professional_id' => $appointment->professional_id,
                        'starts_at' => $appointment->starts_at,
                        'ends_at' => $appointment->ends_at
                            ?: Carbon::parse($appointment->starts_at)->addMinutes(50),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down()
    {
        // Nao removemos slots que podem ter mudado depois do backfill.
    }
};
