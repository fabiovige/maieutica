<?php

namespace Database\Seeders;

use App\Models\Release;
use Illuminate\Database\Seeder;

class ReleaseSeeder extends Seeder
{
    public function run()
    {
        // Janeiro 2026 - v2.0.0
        Release::updateOrCreate(
            ['version' => 'v2.0.0'],
            [
                'title' => 'Seguranca, QA e Campo Estagiario',
                'release_date' => '2026-01-27',
                'description' => 'Melhorias de seguranca para producao, revisao de testes, correcoes de bugs e novo campo estagiario para profissionais.',
                'items' => [
                    'features' => [
                        'Campo estagiario (is_intern) adicionado aos profissionais com checkbox em create/edit e filtro na listagem',
                        'Checklist em modo read-only na edicao (visualizacao protegida)',
                    ],
                    'bugs' => [
                        'Correcao na geracao de PDF',
                        'Correcao de log (fix log viewer)',
                        'Correcao de versao para producao',
                    ],
                    'features' => [
                        'Campo estagiario (is_intern) adicionado aos profissionais com checkbox em create/edit e filtro na listagem',
                        'Checklist em modo read-only na edicao (visualizacao protegida)',
                    ],
                    'seguranca' => [
                        'CORS restrito ao dominio maieuticavalia.com.br',
                        'Sessao criptografada com lifetime de 8 horas',
                        'Security Headers: HSTS, Permissions-Policy',
                        'SESSION_SECURE_COOKIE configurado no .env.example',
                    ],
                    'docs' => [
                        'CLAUDE.md atualizado',
                        'Documento de avaliacao versao_para_prod.md',
                        'Revisao dos testes existentes',
                    ],
                ],
                'commits' => [
                    ['hash' => '77284e6', 'message' => 'Melhorias de seguranca para producao'],
                    ['hash' => '2fe4991', 'message' => 'Atualizacao do CLAUDE.md'],
                    ['hash' => 'e0d0a34', 'message' => 'Revisao dos testes'],
                    ['hash' => 'e046f65', 'message' => 'Read only na edicao de checklists'],
                    ['hash' => 'be06bb1', 'message' => 'Revisao de producao'],
                    ['hash' => 'f9bed44', 'message' => 'Fix: correcao do log'],
                    ['hash' => '6cd33ae', 'message' => 'Fix: versao prod'],
                    ['hash' => '47c7435', 'message' => 'Fix: correcao gerar PDF'],
                    ['hash' => 'b15c4f0', 'message' => 'Feat: adiciona campo estagiario aos profissionais'],
                ],
            ]
        );

        // Fevereiro 2026 - v2.1.0
        Release::updateOrCreate(
            ['version' => 'v2.1.0'],
            [
                'title' => 'Novo Layout Sidebar e Dicionario de Dados',
                'release_date' => '2026-02-09',
                'description' => 'Implementacao do novo layout com sidebar vertical substituindo a navbar horizontal, e criacao do dicionario de dados completo.',
                'items' => [
                    'layout' => [
                        'Novo layout com sidebar vertical fixo (260px, colapsavel para 70px)',
                        'Sidebar responsivo: drawer em mobile com overlay',
                        'Toggle para colapsar sidebar em desktop com icones',
                        'Submenus colapsaveis com animacao',
                        'Estado ativo com borda esquerda destacada',
                        'Estado salvo no localStorage',
                        'Header com breadcrumb a esquerda e perfil a direita',
                    ],
                    'docs' => [
                        'Dicionario de dados completo com 31 tabelas documentadas',
                        'docs/dicionario-dados.md criado',
                    ],
                ],
                'commits' => [
                    ['hash' => '0ab3405', 'message' => 'Dicionario de dados'],
                    ['hash' => '8dd31ff', 'message' => 'Novo layout'],
                    ['hash' => '5e04f48', 'message' => 'Ajuste de fonte novo layout'],
                ],
            ]
        );

        // Marco 2026 - v2.2.0
        Release::updateOrCreate(
            ['version' => 'v2.2.0'],
            [
                'title' => 'Tipografia, E-mails e Ordenacao por Progresso',
                'release_date' => '2026-03-10',
                'description' => 'Padronizacao tipografica completa, redesign de templates de e-mail, correcoes de bugs criticos e novas funcionalidades de ordenacao.',
                'items' => [
                    'tipografia' => [
                        'Fonte base unificada em 16px (1rem) - SCSS, CSS vars, typography.css',
                        'Fonte Nunito (Google Fonts) com pesos 300-800',
                        'Escala tipografica completa para h1-h6, tabelas, forms, botoes, badges',
                        'Sistema de botoes padronizado (_buttons.scss) com paleta clinica/institucional',
                        'Cor primaria rosa #AD6E9B unificada em SCSS e CSS',
                        'CSS vars padronizadas: --fs-base, --fs-xs, --fs-sm, etc.',
                    ],
                    'layout' => [
                        'Submenus dropdown no sidebar (Denver, Prontuarios, Documentos, Cadastros)',
                        'Visualizacao padrao da lista de criancas alterada de cards para tabela',
                    ],
                    'emails' => [
                        'Templates de e-mail redesenhados com visual limpo e profissional (sem emojis)',
                        'Layout padrao institucional: header rosa, corpo neutro cinza, footer clean',
                        'Templates atualizados: Boas-vindas, Conta atualizada, Conta desativada',
                    ],
                    'bugs' => [
                        'Login com senha provisoria: corrigido bug que impedia profissionais de fazer primeiro acesso',
                        'Menu acoes em checklists: corrigido permissao @can seguindo padrao {entity}-{action}[-all]',
                    ],
                    'features' => [
                        'Ordenacao por progresso na lista de criancas (maior/menor primeiro)',
                        'Filtro "Ordenar por" com opcoes: Nome, Progresso, Data',
                        'Colunas Nome e Progresso clicaveis para ordenacao rapida na tabela',
                        'Pagina de Releases para acompanhamento de versoes',
                    ],
                    'docs' => [
                        'CLAUDE.md atualizado com layout sidebar, tipografia, botoes, e-mails',
                        'README.md reescrito com stack atual e design system',
                        'docs/tipografia.md - Auditoria completa de tipografia',
                        'docs/novo-layout-sidebar.md - Documentacao do sidebar',
                    ],
                ],
                'commits' => [
                    ['hash' => 'a577988', 'message' => 'Refatorando layout'],
                    ['hash' => '3344dcf', 'message' => 'Fix: corrigir envio de senha provisoria no cadastro de profissional'],
                    ['hash' => '337dd9a', 'message' => 'Style: redesign templates de e-mail com visual limpo e profissional'],
                    ['hash' => '437fe52', 'message' => 'Docs: atualizar CLAUDE.md e README.md + build de producao'],
                    ['hash' => 'd58604b', 'message' => 'Feat: adicionar dropdowns no sidebar'],
                    ['hash' => 'dd34837', 'message' => 'Fix: corrigir permissao do menu acoes em checklists'],
                    ['hash' => 'f2994a8', 'message' => 'Feat: adicionar ordenacao por progresso na lista de criancas'],
                ],
            ]
        );

        // Marco 2026 - v2.3.0
        Release::updateOrCreate(
            ['version' => 'v2.3.0'],
            [
                'title' => 'Prontuarios Unificados, Filtro Denver e Continuidade Clinica',
                'release_date' => '2026-03-23',
                'description' => 'Unificacao do cadastro de pacientes nos prontuarios, filtro de idade no Denver, historico de prontuarios com continuidade clinica e correcao dos testes.',
                'items' => [
                    'features' => [
                        'Dropdown unico de pacientes nos prontuarios com idade visivel (sem separacao Crianca/Adulto)',
                        'Painel lateral com historico de prontuarios ao selecionar paciente no formulario de criacao',
                        'Secao "Outros Registros deste Paciente" na visualizacao do prontuario (continuidade clinica)',
                        'Secao de prontuarios na pagina de detalhes do paciente (kids/show) com botao "Novo Prontuario"',
                        'Filtro Denver: apenas criancas ate 6 anos aparecem no dropdown de criacao de checklist',
                        'Idade visivel ao lado do nome em todos os dropdowns de pacientes',
                        'Rota patient-history para busca de historico via AJAX',
                        'Rota history mapeada para historico de versoes de prontuarios',
                    ],
                    'bugs' => [
                        'Correcao: adultos cadastrados na tabela kids agora aparecem nos prontuarios sem precisar selecionar "Crianca"',
                        'Correcao: UserFactory com role_id inexistente na tabela users',
                        'Correcao: KidFactory com profession_id inexistente na tabela kids',
                        'Correcao: ChecklistFactory com dependencia de dados existentes no banco',
                        'Correcao: memory_limit insuficiente no phpunit.xml (aumentado para 512M)',
                    ],
                    'docs' => [
                        'docs/fix-001.md - Investigacao detalhada dos problemas de pacientes, Denver e continuidade',
                        'docs/jira-001.md - Plano de implementacao em 3 fases com decisoes tecnicas',
                    ],
                ],
                'commits' => [
                    ['hash' => '9841c69', 'message' => 'Fix: unificar pacientes nos prontuarios, filtrar Denver por idade e adicionar continuidade'],
                ],
            ]
        );
        // Marco 2026 - v2.4.0
        Release::updateOrCreate(
            ['version' => 'v2.4.0'],
            [
                'title' => 'Observabilidade, Remocao do Telescope e Correcoes de UI',
                'release_date' => '2026-03-26',
                'description' => 'Endpoint de health check, notificacao de erros por e-mail em producao, remocao completa do Telescope, suporte polimórfico completo nos prontuarios e correcoes visuais.',
                'items' => [
                    'features' => [
                        'Endpoint /health sem autenticacao para monitoramento externo (banco, cache, disco, fila)',
                        'Notificacao de erros criticos por e-mail ao admin em producao (substitui Sentry)',
                        'Prontuarios: suporte polimórfico completo a pacientes adultos (User) alem de criancas (Kid)',
                        'Prontuarios: filtro de patient_type na busca da listagem',
                        'Select2 aplicado no dropdown de crianca no formulario de criacao de checklist',
                        'Checklist create: nivel fixo em 4 Denver (sem seletor manual)',
                    ],
                    'bugs' => [
                        'Denver: escopo de elegibilidade corrigido para 60 meses (5 anos) - era YEAR impreciso',
                        'Dropdown em tabelas: corrigido corte por overflow com position fixed via JS',
                        'PDF: pagina exclusiva de assinatura com page-break-before (evita assinatura cortada)',
                        'MedicalRecordRequest: session_date opcional no PUT (apenas obrigatoria no POST)',
                    ],
                    'chores' => [
                        'Remocao completa do Telescope: provider, assets publicados e padrão no Debugbar',
                        'Rota de teste do Sentry removida',
                    ],
                    'ui' => [
                        'Espacamento entre card de info da crianca e lista de checklists (mb-3)',
                        'Badge de status Fechado nos checklists com cinza opaco (opacity-75)',
                        'Border-radius nas celulas extremas da tabela (sem overflow hidden)',
                        'CSS: variavel --color-blue-primary, .bg-primary usa azul institucional',
                        'Sidebar: link de Logs removido do menu lateral',
                        'table-actions: shadow e auto-close no dropdown',
                    ],
                ],
                'commits' => [
                    ['hash' => '8b56874', 'message' => 'feat: suporte a pacientes adultos nos prontuarios, health check e correcoes de UI'],
                    ['hash' => 'd938d6d', 'message' => 'chore: remover todos os vestigios do Telescope'],
                    ['hash' => '5121cca', 'message' => 'style: espacamento e badge opaco para checklists fechados'],
                ],
            ]
        );
    }
}
