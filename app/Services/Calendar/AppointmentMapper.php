<?php

namespace App\Services\Calendar;

use App\Models\Appointment;
use Carbon\Carbon;

/**
 * Traduz um evento do Google Calendar nos campos da tabela `appointments`.
 *
 * O fluxo N8N grava os dados do atendimento como TEXTO LIVRE na descricao do
 * evento (ver n8n/fluxo-atendimento.json, no "Criar Evento"). O parsing abaixo
 * e best-effort de proposito: eventos criados manualmente pela clinica nao
 * seguem esse formato e mesmo assim precisam entrar na fila de confirmacao,
 * com os campos vazios para a recepcao completar.
 */
class AppointmentMapper
{
    /**
     * Rotulos das linhas escritas pelo N8N na descricao do evento.
     */
    private const FIELD_LABELS = [
        'reason' => 'Motivo da consulta',
        'specialty_raw' => 'Especialidade',
        'professional_raw' => 'Profissional',
        'patient_email' => 'E-mail do cliente',
        'patient_phone' => 'Telefone',
    ];

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    public function toAttributes(array $event): array
    {
        $description = (string) ($event['description'] ?? '');

        return [
            'starts_at' => $this->parseDateTime($event['start'] ?? []),
            'ends_at' => $this->parseDateTime($event['end'] ?? []),
            'patient_name' => $this->parsePatientName($event['summary'] ?? null),
            'patient_email' => $this->parseField($description, 'patient_email'),
            'patient_phone' => $this->parseField($description, 'patient_phone'),
            'reason' => $this->parseField($description, 'reason'),
            'specialty_raw' => $this->parseField($description, 'specialty_raw'),
            'professional_raw' => $this->parseField($description, 'professional_raw'),
        ];
    }

    /**
     * Evento cancelado no Google Calendar.
     *
     * @param  array<string, mixed>  $event
     */
    public function isCanceled(array $event): bool
    {
        return ($event['status'] ?? null) === 'cancelled';
    }

    /**
     * O N8N cria o evento com summary "Agendamento: <nome do cliente>".
     * Sem esse prefixo, mantemos o titulo original como referencia.
     */
    private function parsePatientName(?string $summary): ?string
    {
        $summary = trim((string) $summary);

        if ($summary === '') {
            return null;
        }

        if (preg_match('/^Agendamento:\s*(.+)$/iu', $summary, $matches)) {
            return trim($matches[1]);
        }

        return $summary;
    }

    private function parseField(string $description, string $field): ?string
    {
        if ($description === '' || ! isset(self::FIELD_LABELS[$field])) {
            return null;
        }

        $label = preg_quote(self::FIELD_LABELS[$field], '/');

        if (! preg_match('/^\s*'.$label.'\s*:\s*(.*)$/miu', $description, $matches)) {
            return null;
        }

        $value = trim($matches[1]);

        return $value === '' ? null : $value;
    }

    /**
     * Eventos normais trazem `dateTime`; eventos de dia inteiro trazem `date`.
     *
     * @param  array<string, mixed>  $slot
     */
    private function parseDateTime(array $slot): ?Carbon
    {
        $value = $slot['dateTime'] ?? $slot['date'] ?? null;

        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->setTimezone(config('app.timezone'));
    }

    /**
     * Situacao inicial de um agendamento recem-importado.
     */
    public function initialSituation(): string
    {
        return Appointment::SITUATION_PENDING;
    }
}
