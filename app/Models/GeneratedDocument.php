<?php

namespace App\Models;

class GeneratedDocument extends BaseModel
{
    protected $fillable = [
        'model_type',
        'documentable_id',
        'documentable_type',
        'professional_id',
        'generated_by',
        'html_content',
        'form_data',
        'metadata',
        'generated_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'metadata' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Relacionamento polimórfico - pode ser Kid ou User
     */
    public function documentable()
    {
        return $this->morphTo();
    }

    /**
     * Profissional que assinou o documento
     */
    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    /**
     * Usuário que gerou o documento
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Accessor para nome legível do tipo de documento
     */
    public function getModelTypeNameAttribute()
    {
        return match ($this->model_type) {
            1 => 'Declaração - Modelo 1',
            2 => 'Declaração Simplificada - Modelo 2',
            3 => 'Laudo Psicológico - Modelo 3',
            4 => 'Parecer Psicológico - Modelo 4',
            5 => 'Relatório Multiprofissional - Modelo 5',
            6 => 'Relatório Psicológico - Modelo 6',
            default => 'Documento Desconhecido',
        };
    }

    /**
     * Accessor para nome do arquivo PDF
     */
    public function getFilenameAttribute()
    {
        $type = str_replace([' - Modelo ', ' '], ['_', '_'], $this->model_type_name);
        $type = strtolower($type);
        // Remove acentos
        $type = preg_replace('/[áàãâä]/u', 'a', $type);
        $type = preg_replace('/[éèêë]/u', 'e', $type);
        $type = preg_replace('/[íìîï]/u', 'i', $type);
        $type = preg_replace('/[óòõôö]/u', 'o', $type);
        $type = preg_replace('/[úùûü]/u', 'u', $type);
        $type = preg_replace('/[ç]/u', 'c', $type);

        $date = $this->generated_at->format('Y-m-d');

        return "{$type}_{$date}.pdf";
    }

    /**
     * Scope para filtrar documentos de Kids
     */
    public function scopeForKids($query)
    {
        return $query->where('documentable_type', Kid::class);
    }

    /**
     * Scope para filtrar documentos de Users
     */
    public function scopeForUsers($query)
    {
        return $query->where('documentable_type', User::class);
    }

    /**
     * Scope para respeitar permissões do usuário autenticado
     */
    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        // Admin vê tudo
        if ($user->can('document-list-all')) {
            return $query;
        }

        // Profissional vê documentos gerados por ele ou de seus pacientes
        $professional = $user->professional->first();

        if ($professional) {
            return $query->where(function ($q) use ($user, $professional) {
                $q->where('generated_by', $user->id)
                    ->orWhere('professional_id', $professional->id);
            });
        }

        // Outros veem apenas os que geraram
        return $query->where('generated_by', $user->id);
    }
}
