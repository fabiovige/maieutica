<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'html_content',
        'description',
        'available_placeholders',
        'version',
        'is_active',
    ];

    protected $casts = [
        'available_placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Template has many generated documents
     */
    public function generatedDocuments()
    {
        return $this->hasMany(GeneratedDocument::class);
    }

    /**
     * Scope: Only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get all available placeholder categories
     */
    public static function getAvailablePlaceholderCategories()
    {
        return [
            'crianca' => [
                'nome_crianca' => 'Nome da criança',
                'idade' => 'Idade da criança',
                'data_nascimento' => 'Data de nascimento',
                'responsavel' => 'Nome do responsável',
                'genero' => 'Gênero',
                'etnia' => 'Etnia',
            ],
            'profissional' => [
                'profissional_nome' => 'Nome do profissional',
                'profissional_crp' => 'CRP do profissional',
                'profissional_especialidade' => 'Especialidade do profissional',
            ],
            'atendimento' => [
                'data_inicio' => 'Data de início do atendimento',
                'total_sessoes' => 'Total de sessões realizadas',
            ],
            'avaliacao' => [
                'data_avaliacao' => 'Data da avaliação',
                'percentual_desenvolvimento' => 'Percentual de desenvolvimento',
                'competencias_desenvolvidas' => 'Competências desenvolvidas',
            ],
            'sistema' => [
                'data_emissao' => 'Data de emissão do documento',
                'hora_emissao' => 'Hora de emissão do documento',
                'numero_documento' => 'Número do documento',
            ],
        ];
    }

    /**
     * Get flat list of all available placeholders
     */
    public static function getAllPlaceholders()
    {
        $categories = self::getAvailablePlaceholderCategories();
        $placeholders = [];

        foreach ($categories as $category => $fields) {
            foreach ($fields as $key => $label) {
                $placeholders[$key] = $label;
            }
        }

        return $placeholders;
    }
}
