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
                'nome_completo' => 'Nome completo da pessoa atendida',
                'nome_crianca' => 'Nome da criança',
                'cpf' => 'CPF',
                'idade' => 'Idade',
                'sexo' => 'Sexo/Gênero',
                'data_nascimento' => 'Data de nascimento',
            ],
            'responsavel' => [
                'nome_responsavel' => 'Nome do responsável',
                'nome_acompanhante' => 'Nome do acompanhante',
                'nome_informante' => 'Nome de quem forneceu informações',
            ],
            'profissional' => [
                'profissional_nome' => 'Nome completo do profissional',
                'profissional_crp' => 'Número de inscrição no CRP',
                'profissional_especialidade' => 'Especialidade do profissional',
                'profissionais_multiplos' => 'Lista de profissionais (relatório multiprofissional)',
            ],
            'atendimento' => [
                'data_inicio' => 'Data/mês/ano de início do atendimento',
                'dias_semana' => 'Dias da semana das sessões',
                'horarios' => 'Horários das sessões',
                'data_termino' => 'Data de término (se houver)',
                'numero_encontros' => 'Número de encontros realizados',
                'duracao_encontros' => 'Duração dos encontros (em horas)',
                'total_sessoes' => 'Total de sessões realizadas',
            ],
            'avaliacao' => [
                'solicitante' => 'Quem solicitou o documento',
                'finalidade' => 'Finalidade do documento',
                'sintomas_relatados' => 'Descrição dos sintomas relatados',
                'consequencias' => 'Consequências relatadas',
                'hipotese_diagnostico' => 'Hipótese ou diagnóstico',
                'recursos_tecnicos' => 'Recursos técnicos utilizados',
                'referencial_teorico' => 'Referencial teórico metodológico',
                'pessoas_ouvidas' => 'Pessoas ouvidas no processo',
                'analise_caracteristicas' => 'Análise de características e evolução',
                'diagnostico' => 'Diagnóstico formal',
                'cid' => 'Código CID',
                'prognostico' => 'Prognóstico',
                'encaminhamentos' => 'Encaminhamentos e orientações',
                'referencias' => 'Referências bibliográficas',
            ],
            'sistema' => [
                'data_emissao' => 'Data de emissão do documento',
                'cidade' => 'Cidade',
                'numero_documento' => 'Número sequencial do documento',
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

    /**
     * Get available document types
     */
    public static function getDocumentTypes()
    {
        return [
            'declaracao' => 'Declaração',
            'laudo' => 'Laudo Psicológico',
            'parecer' => 'Parecer Psicológico',
            'relatorio' => 'Relatório Psicológico',
            'relatorio_multi' => 'Relatório Multiprofissional',
        ];
    }
}
