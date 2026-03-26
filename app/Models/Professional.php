<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Professional extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'registration_number',
        'council',
        'bio',
        'is_intern',
        'specialty_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    const COUNCILS = [
        'CRP'     => 'CRP — Conselho Regional de Psicologia',
        'CREFITO' => 'CREFITO — Fisioterapia e Terapia Ocupacional',
        'CRFa'    => 'CRFa — Conselho Regional de Fonoaudiologia',
        'CRM'     => 'CRM — Conselho Regional de Medicina',
        'COREN'   => 'COREN — Conselho Regional de Enfermagem',
        'CREF'    => 'CREF — Conselho Regional de Educação Física',
        'CRN'     => 'CRN — Conselho Regional de Nutrição',
        'CRESS'   => 'CRESS — Conselho Regional de Serviço Social',
        'ABPp'    => 'ABPp — Associação Brasileira de Psicopedagogia',
        'UBM'     => 'UBM — União Brasileira de Musicoterapia',
        'Outro'   => 'Outro',
    ];

    protected $casts = [
        'is_intern' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'user_professional');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function kids()
    {
        return $this->belongsToMany(Kid::class, 'kid_professional')
            ->whereNull('kids.deleted_at');
    }

    /**
     * Retorna o label do conselho profissional (valor salvo ou fallback por especialidade).
     */
    public function getCouncilLabelAttribute(): string
    {
        if ($this->council) {
            return $this->council;
        }

        $map = [
            'Psicologia'               => 'CRP',
            'Psicopedagogia'           => 'CRP',
            'Fisioterapia'             => 'CREFITO',
            'Terapia Ocupacional'      => 'CREFITO',
            'Fonoaudiologia'           => 'CRFa',
            'Pediatria'                => 'CRM',
            'Neurologia Infantil'      => 'CRM',
            'Psiquiatria Infantil'     => 'CRM',
            'Enfermagem Pediátrica'    => 'COREN',
            'Educação Física Infantil' => 'CREF',
            'Nutrição Infantil'        => 'CRN',
            'Assistência Social'       => 'CRESS',
            'Psicomotricidade'         => 'ABPp',
            'Musicoterapia'            => 'UBM',
        ];

        return $map[$this->specialty?->name ?? ''] ?? 'Reg.';
    }

    /**
     * Users that this professional attends (as patients)
     */
    public function patients()
    {
        return $this->belongsToMany(User::class, 'professional_user_patient')
            ->whereNull('users.deleted_at')
            ->where('users.allow', 1);
    }
}
