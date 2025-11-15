<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    public function run()
    {
        // Template 1: Declaração Simples
        DocumentTemplate::create([
            'name' => 'Declaração Simples',
            'type' => 'declaracao',
            'description' => 'Modelo de declaração simples de acompanhamento psicológico',
            'version' => '1.0',
            'is_active' => true,
            'available_placeholders' => array_keys(DocumentTemplate::getAllPlaceholders()),
            'html_content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.6; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .title { font-size: 16pt; font-weight: bold; margin: 20px 0; text-align: center; }
        .content { text-align: justify; margin: 20px 0; }
        .signature { margin-top: 80px; text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <strong>{{profissional_nome}}</strong><br>
        {{profissional_titulo}}<br>
        {{profissional_registro}}<br>
        {{profissional_telefone}}
    </div>

    <div class="title">DECLARAÇÃO</div>

    <div class="content">
        <p>Declaro para os devidos fins que <strong>{{nome_completo}}</strong>, nascido(a) em <strong>{{data_nascimento}}</strong>, CPF <strong>{{cpf}}</strong>, está em acompanhamento psicológico nesta clínica desde <strong>{{mes_inicio}}/{{ano_inicio}}</strong>.</p>

        <p>O atendimento ocorre <strong>{{dias_semana}}</strong>, com sessões de <strong>{{duracao_sessao}}</strong>.</p>

        <p>Declaro ainda que o(a) paciente está em tratamento contínuo e responde adequadamente às intervenções propostas.</p>
    </div>

    <div class="content">
        <p>{{cidade}}, {{data_emissao}}.</p>
    </div>

    <div class="signature">
        <div class="signature-line">
            <strong>{{profissional_nome}}</strong><br>
            {{profissional_registro}}
        </div>
    </div>
</body>
</html>
            ',
        ]);

        // Template 2: Declaração com Horários
        DocumentTemplate::create([
            'name' => 'Declaração com Horários Detalhados',
            'type' => 'declaracao',
            'description' => 'Declaração com detalhamento de dias, horários e opções de término',
            'version' => '1.0',
            'is_active' => true,
            'available_placeholders' => array_keys(DocumentTemplate::getAllPlaceholders()),
            'html_content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.6; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .title { font-size: 16pt; font-weight: bold; margin: 20px 0; text-align: center; }
        .content { text-align: justify; margin: 20px 0; }
        .signature { margin-top: 80px; text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <strong>{{profissional_nome}}</strong><br>
        {{profissional_titulo}}<br>
        {{profissional_registro}}<br>
        {{profissional_telefone}}
    </div>

    <div class="title">DECLARAÇÃO</div>

    <div class="content">
        <p>Declaro para os devidos fins que <strong>{{nome_completo}}</strong>, nascido(a) em <strong>{{data_nascimento}}</strong>, filho(a) de <strong>{{nome_responsavel}}</strong>, residente à <strong>{{endereco}}</strong>, está em acompanhamento psicológico nesta clínica desde <strong>{{mes_inicio}}/{{ano_inicio}}</strong>.</p>

        <p>As sessões ocorrem às <strong>{{dias_semana}}</strong> às <strong>{{horario_atendimento}}</strong>, com duração de <strong>{{duracao_sessao}}</strong> cada.</p>

        <p><strong>{{opcao_termino}}</strong></p>
        <!-- Opcões de término:
             - "O tratamento encontra-se em andamento."
             - "O tratamento foi finalizado em [DATA]."
             - "O acompanhamento terá continuidade até [DATA]."
        -->
    </div>

    <div class="content">
        <p>Por ser verdade, firmo a presente declaração.</p>
        <p>{{cidade}}, {{data_emissao}}.</p>
    </div>

    <div class="signature">
        <div class="signature-line">
            <strong>{{profissional_nome}}</strong><br>
            {{profissional_registro}}
        </div>
    </div>
</body>
</html>
            ',
        ]);

        // Template 3: Laudo Psicológico
        DocumentTemplate::create([
            'name' => 'Laudo Psicológico Completo',
            'type' => 'laudo_psicologico',
            'description' => 'Laudo psicológico completo com 6 seções estruturadas e CID',
            'version' => '1.0',
            'is_active' => true,
            'available_placeholders' => array_keys(DocumentTemplate::getAllPlaceholders()),
            'html_content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .title { font-size: 16pt; font-weight: bold; margin: 20px 0; text-align: center; text-transform: uppercase; }
        .section { margin: 25px 0; page-break-inside: avoid; }
        .section-title { font-size: 12pt; font-weight: bold; margin: 15px 0 10px 0; text-transform: uppercase; border-bottom: 1px solid #666; }
        .content { text-align: justify; margin: 10px 0; }
        .signature { margin-top: 80px; text-align: center; page-break-inside: avoid; }
        .signature-line { border-top: 1px solid #000; width: 350px; margin: 0 auto; padding-top: 5px; }
        .patient-info { background-color: #f5f5f5; padding: 15px; border-left: 4px solid #333; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <strong style="font-size: 14pt;">{{profissional_nome}}</strong><br>
        {{profissional_titulo}}<br>
        {{profissional_registro}}<br>
        {{profissional_telefone}} | {{profissional_email}}
    </div>

    <div class="title">Laudo Psicológico</div>

    <div class="patient-info">
        <strong>Identificação do(a) Paciente:</strong><br>
        Nome: {{nome_completo}}<br>
        Data de Nascimento: {{data_nascimento}} | Idade: {{idade}} anos<br>
        CPF: {{cpf}}<br>
        Responsável: {{nome_responsavel}} ({{telefone_responsavel}})<br>
        Endereço: {{endereco}}<br>
        Solicitante: {{solicitante}}
    </div>

    <div class="section">
        <div class="section-title">1. Descrição da Demanda</div>
        <div class="content">
            <p>{{descricao_demanda}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">2. Procedimentos e Instrumentos Utilizados</div>
        <div class="content">
            <p>{{instrumentos_utilizados}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">3. Análise dos Resultados</div>
        <div class="content">
            <p>{{analise_resultados}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">4. Diagnóstico e Hipótese Diagnóstica</div>
        <div class="content">
            <p><strong>Diagnóstico:</strong> {{diagnostico}}</p>
            <p><strong>CID:</strong> {{cid}}</p>
            <p>{{hipotese_diagnostica}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">5. Prognóstico</div>
        <div class="content">
            <p>{{prognostico}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">6. Recomendações e Encaminhamentos</div>
        <div class="content">
            <p>{{recomendacoes}}</p>
        </div>
    </div>

    <div class="content" style="margin-top: 40px;">
        <p>{{cidade}}, {{data_emissao}}.</p>
    </div>

    <div class="signature">
        <div class="signature-line">
            <strong>{{profissional_nome}}</strong><br>
            {{profissional_titulo}}<br>
            {{profissional_registro}}
        </div>
    </div>

    <div style="margin-top: 40px; font-size: 9pt; color: #666; text-align: center;">
        Documento nº {{numero_documento}} | Gerado em {{data_emissao}}
    </div>
</body>
</html>
            ',
        ]);

        // Template 4: Parecer Psicológico
        DocumentTemplate::create([
            'name' => 'Parecer Psicológico',
            'type' => 'parecer_psicologico',
            'description' => 'Parecer psicológico estruturado com 5 seções',
            'version' => '1.0',
            'is_active' => true,
            'available_placeholders' => array_keys(DocumentTemplate::getAllPlaceholders()),
            'html_content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .title { font-size: 16pt; font-weight: bold; margin: 20px 0; text-align: center; text-transform: uppercase; }
        .section { margin: 20px 0; page-break-inside: avoid; }
        .section-title { font-size: 12pt; font-weight: bold; margin: 15px 0 10px 0; }
        .content { text-align: justify; margin: 10px 0; }
        .signature { margin-top: 60px; text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <strong style="font-size: 14pt;">{{profissional_nome}}</strong><br>
        {{profissional_titulo}}<br>
        {{profissional_registro}}
    </div>

    <div class="title">Parecer Psicológico</div>

    <div class="content">
        <p><strong>Identificação:</strong> {{nome_completo}}, {{idade}} anos, nascido(a) em {{data_nascimento}}.</p>
        <p><strong>Solicitante:</strong> {{solicitante}}</p>
    </div>

    <div class="section">
        <div class="section-title">1. Objetivo do Parecer</div>
        <div class="content">
            <p>{{objetivo_parecer}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">2. Histórico Clínico</div>
        <div class="content">
            <p>{{historico_clinico}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">3. Avaliação Psicológica</div>
        <div class="content">
            <p>{{avaliacao_psicologica}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">4. Conclusão</div>
        <div class="content">
            <p>{{conclusao}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">5. Recomendações</div>
        <div class="content">
            <p>{{recomendacoes}}</p>
        </div>
    </div>

    <div class="content" style="margin-top: 30px;">
        <p>{{cidade}}, {{data_emissao}}.</p>
    </div>

    <div class="signature">
        <div class="signature-line">
            <strong>{{profissional_nome}}</strong><br>
            {{profissional_registro}}
        </div>
    </div>
</body>
</html>
            ',
        ]);

        // Template 5: Relatório Psicológico Simples
        DocumentTemplate::create([
            'name' => 'Relatório Psicológico Simples',
            'type' => 'relatorio_psicologico',
            'description' => 'Relatório psicológico mais enxuto para acompanhamento',
            'version' => '1.0',
            'is_active' => true,
            'available_placeholders' => array_keys(DocumentTemplate::getAllPlaceholders()),
            'html_content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.6; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .title { font-size: 16pt; font-weight: bold; margin: 20px 0; text-align: center; }
        .section { margin: 20px 0; }
        .section-title { font-size: 12pt; font-weight: bold; margin: 15px 0 10px 0; }
        .content { text-align: justify; margin: 10px 0; }
        .signature { margin-top: 60px; text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <strong>{{profissional_nome}}</strong><br>
        {{profissional_titulo}}<br>
        {{profissional_registro}}
    </div>

    <div class="title">Relatório Psicológico</div>

    <div class="content">
        <p><strong>Paciente:</strong> {{nome_completo}}</p>
        <p><strong>Data de Nascimento:</strong> {{data_nascimento}} | <strong>Idade:</strong> {{idade}} anos</p>
        <p><strong>Responsável:</strong> {{nome_responsavel}}</p>
        <p><strong>Período de Atendimento:</strong> {{mes_inicio}}/{{ano_inicio}} até {{data_emissao}}</p>
    </div>

    <div class="section">
        <div class="section-title">Motivo do Atendimento</div>
        <div class="content">
            <p>{{descricao_demanda}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Desenvolvimento do Processo</div>
        <div class="content">
            <p>{{desenvolvimento_processo}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Evolução e Observações</div>
        <div class="content">
            <p>{{evolucao}}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Considerações Finais</div>
        <div class="content">
            <p>{{consideracoes_finais}}</p>
        </div>
    </div>

    <div class="content" style="margin-top: 30px;">
        <p>{{cidade}}, {{data_emissao}}.</p>
    </div>

    <div class="signature">
        <div class="signature-line">
            <strong>{{profissional_nome}}</strong><br>
            {{profissional_registro}}
        </div>
    </div>
</body>
</html>
            ',
        ]);
    }
}
