<?php

namespace App\Services\Logging;

use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Logging centralizado das operacoes de agendamento.
 *
 * Privacidade (LGPD): a tela expoe nome, telefone, e-mail e motivo da consulta
 * (dado de saude). Os logs registram apenas identificadores e metadados -
 * nunca o conteudo dos campos pessoais.
 */
class AppointmentLogger
{
    public function listed(array $context = []): void
    {
        Log::info('Appointments listed', array_merge($this->buildUserContext(), $context));
    }

    public function confirmed(Appointment $appointment, array $context = []): void
    {
        Log::notice('Appointment confirmed', array_merge([
            'appointment_id' => $appointment->id,
            'google_event_id' => $appointment->google_event_id,
            'professional_id' => $appointment->professional_id,
            'starts_at' => $appointment->starts_at?->toDateTimeString(),
        ], $this->buildUserContext(), $context));
    }

    public function refused(Appointment $appointment, array $context = []): void
    {
        Log::notice('Appointment refused', array_merge([
            'appointment_id' => $appointment->id,
            'google_event_id' => $appointment->google_event_id,
            'starts_at' => $appointment->starts_at?->toDateTimeString(),
        ], $this->buildUserContext(), $context));
    }

    private function buildUserContext(): array
    {
        return [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
        ];
    }
}
