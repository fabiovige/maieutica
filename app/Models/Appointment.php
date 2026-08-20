<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Agendamento originado no Google Calendar (criado pelo fluxo N8N via WhatsApp).
 *
 * Divisao de responsabilidade:
 * - Google Calendar e dono da existencia e do horario do evento;
 * - o Maieutica e dono do estado de confirmacao e do vinculo com o profissional.
 *
 * O comando `agenda:sync` nunca sobrescreve os campos de confirmacao.
 */
class Appointment extends BaseModel
{
    public const SITUATION = [
        'p' => 'Pendente',
        'c' => 'Confirmado',
        'r' => 'Recusado',
        'x' => 'Cancelado',
    ];

    public const SITUATION_PENDING = 'p';

    public const SITUATION_CONFIRMED = 'c';

    public const SITUATION_REFUSED = 'r';

    public const SITUATION_CANCELED = 'x';

    protected $fillable = [
        'google_event_id',
        'starts_at',
        'ends_at',
        'patient_name',
        'patient_email',
        'patient_phone',
        'reason',
        'specialty_raw',
        'professional_raw',
        'professional_id',
        'situation',
        'confirmed_by',
        'confirmed_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function getSituationLabelAttribute(): string
    {
        return self::SITUATION[$this->situation] ?? $this->situation;
    }

    public function isConfirmed(): bool
    {
        return $this->situation === self::SITUATION_CONFIRMED;
    }

    /**
     * Agendamentos visiveis ao usuario autenticado.
     *
     * appointment-list-all ve todos, em qualquer situacao (fila de confirmacao).
     * Os demais veem apenas os proprios agendamentos ja confirmados.
     */
    public function scopeVisibleToAuthUser(Builder $query): Builder
    {
        if (auth()->user()->can('appointment-list-all')) {
            return $query;
        }

        $professionalId = auth()->user()->professional->first()?->id;

        // Sem professional vinculado nao ha agenda propria para exibir.
        // Explicito porque `where('professional_id', null)` viraria `IS NULL`
        // no Eloquent, expondo agendamentos ainda sem profissional definido.
        if (! $professionalId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('situation', self::SITUATION_CONFIRMED)
            ->where('professional_id', $professionalId);
    }
}
