<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\Calendar\AppointmentMapper;
use App\Services\Calendar\GoogleCalendarClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Espelha os eventos do Google Calendar na tabela `appointments`.
 *
 * O Calendar e a fonte da existencia e do horario do evento; o estado de
 * confirmacao pertence ao Maieutica e NUNCA e sobrescrito por este comando.
 */
class SyncGoogleCalendarAppointments extends Command
{
    protected $signature = 'agenda:sync
                            {--dry-run : Apenas relata o que seria criado/atualizado, sem gravar}';

    protected $description = 'Sincroniza os agendamentos do Google Calendar para a tabela appointments';

    public function handle(GoogleCalendarClient $client, AppointmentMapper $mapper): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $windowDays = (int) config('services.google_calendar.sync_window_days', 60);

        // Integracao ainda nao configurada: sai limpo em vez de falhar a cada
        // 5 minutos no scheduler, poluindo o log de erros.
        if (blank(config('services.google_calendar.calendar_id')) || blank(config('services.google_calendar.credentials_path'))) {
            $this->warn('Integracao com o Google Calendar nao configurada (GOOGLE_CALENDAR_ID / GOOGLE_CALENDAR_CREDENTIALS). Nada a sincronizar.');

            return self::SUCCESS;
        }

        $from = now()->startOfDay();
        $to = now()->addDays($windowDays)->endOfDay();

        $this->info("Consultando eventos de {$from->format('d/m/Y')} a {$to->format('d/m/Y')}...");

        try {
            $events = $client->listEvents($from, $to);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            Log::error('Falha ao sincronizar a agenda do Google Calendar', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }

        $created = $updated = $canceled = $reopened = $skipped = 0;

        foreach ($events as $event) {
            $googleEventId = $event['id'] ?? null;

            if (blank($googleEventId)) {
                $skipped++;

                continue;
            }

            $appointment = Appointment::withTrashed()->firstWhere('google_event_id', $googleEventId);
            $attributes = $mapper->toAttributes($event);

            // Evento cancelado no Calendar: marca cancelado preservando o
            // historico de confirmacao. Nunca apaga a linha.
            if ($mapper->isCanceled($event)) {
                if ($appointment && $appointment->situation !== Appointment::SITUATION_CANCELED) {
                    $canceled++;
                    if (! $dryRun) {
                        $appointment->update(['situation' => Appointment::SITUATION_CANCELED]);
                    }
                }

                continue;
            }

            // Sem horario de inicio nao ha agendamento utilizavel.
            if (blank($attributes['starts_at'])) {
                $skipped++;

                continue;
            }

            if (! $appointment) {
                $created++;

                if (! $dryRun) {
                    Appointment::create($attributes + [
                        'google_event_id' => $googleEventId,
                        'situation' => $mapper->initialSituation(),
                    ]);
                }

                continue;
            }

            // Horario alterado no Calendar depois de confirmado: volta para a
            // fila para a recepcao reconfirmar, em vez de divergir em silencio.
            $timeChanged = ! $appointment->starts_at->equalTo($attributes['starts_at']);

            if ($timeChanged && $appointment->isConfirmed()) {
                $attributes['situation'] = Appointment::SITUATION_PENDING;
                $attributes['confirmed_by'] = null;
                $attributes['confirmed_at'] = null;
                $reopened++;

                Log::notice('Agendamento confirmado teve o horario alterado no Google Calendar', [
                    'appointment_id' => $appointment->id,
                    'google_event_id' => $googleEventId,
                    'starts_at_anterior' => $appointment->starts_at->toDateTimeString(),
                    'starts_at_novo' => $attributes['starts_at']->toDateTimeString(),
                ]);
            }

            $updated++;

            if (! $dryRun) {
                // Apenas os campos espelhados do Calendar: situation,
                // professional_id e confirmacao pertencem ao Maieutica.
                $appointment->update($attributes);
            }
        }

        $this->newLine();
        $this->line('Eventos recebidos:  '.count($events));
        $this->line("Criados:            {$created}");
        $this->line("Atualizados:        {$updated}");
        $this->line("Cancelados:         {$canceled}");
        $this->line("Reabertos:          {$reopened}");
        $this->line("Ignorados:          {$skipped}");

        if ($dryRun) {
            $this->newLine();
            $this->warn('Execucao em --dry-run: nada foi gravado.');
        }

        return self::SUCCESS;
    }
}
