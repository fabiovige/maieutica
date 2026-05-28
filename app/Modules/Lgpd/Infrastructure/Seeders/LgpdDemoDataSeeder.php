<?php

namespace App\Modules\Lgpd\Infrastructure\Seeders;

use App\Models\Kid;
use App\Models\User;
use App\Modules\Lgpd\Infrastructure\Models\AccessLogModel;
use App\Modules\Lgpd\Infrastructure\Models\ConsentRecordModel;
use App\Modules\Lgpd\Infrastructure\Models\DataRequestModel;
use App\Modules\Lgpd\Infrastructure\Models\RetentionPolicyModel;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder de dados de demonstração para o módulo LGPD.
 *
 * Uso: docker exec -it maieutica_app php artisan db:seed --class="App\Modules\Lgpd\Infrastructure\Seeders\LgpdDemoDataSeeder"
 */
class LgpdDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $operator = User::first();
        $kids = Kid::take(5)->get();

        if ($kids->isEmpty()) {
            $this->command->warn('Nenhum paciente (kid) encontrado. Crie pacientes antes de rodar este seeder.');
            return;
        }

        $this->command->info('Criando dados de demonstração LGPD...');

        $this->seedConsents($kids, $operator);
        $this->seedDataRequests($operator);
        $this->seedAccessLogs($kids, $operator);
        $this->seedRetentionPolicies($operator);

        $this->command->info('Dados de demonstração LGPD criados com sucesso!');
    }

    private function seedConsents($kids, User $operator): void
    {
        $purposes = [
            'Tratamento de dados para acompanhamento terapêutico',
            'Compartilhamento de relatórios com escola',
            'Envio de comunicações por e-mail',
            'Armazenamento de fotos e vídeos de sessões',
            'Compartilhamento de dados com convênio',
        ];

        $legalBases = [
            'consentimento',
            'execucao_contrato',
            'tutela_saude',
            'obrigacao_legal',
            'legitimo_interesse',
        ];

        foreach ($kids->take(3) as $index => $kid) {
            // Consentimento ativo
            ConsentRecordModel::firstOrCreate(
                ['subject_id' => $kid->id, 'purpose' => $purposes[$index], 'status' => 'ativo'],
                [
                    'subject_type' => 'kid',
                    'legal_basis' => $legalBases[$index],
                    'term_version' => 1,
                    'collected_at' => Carbon::now()->subDays(rand(30, 180)),
                    'collected_by' => $operator->id,
                ]
            );
        }

        // Um consentimento revogado
        if ($kids->count() >= 4) {
            ConsentRecordModel::firstOrCreate(
                ['subject_id' => $kids[3]->id, 'purpose' => $purposes[3], 'status' => 'revogado'],
                [
                    'subject_type' => 'kid',
                    'legal_basis' => 'consentimento',
                    'term_version' => 1,
                    'collected_at' => Carbon::now()->subDays(200),
                    'collected_by' => $operator->id,
                    'revoked_at' => Carbon::now()->subDays(15),
                    'revoked_by' => $operator->id,
                ]
            );
        }

        $this->command->info('  ✓ ' . ConsentRecordModel::count() . ' consentimentos criados');
    }

    private function seedDataRequests(User $operator): void
    {
        $requests = [
            [
                'type' => 'acesso',
                'requester_name' => 'Maria Silva Santos',
                'requester_document' => '12345678901',
                'contact_method' => 'maria.silva@email.com',
                'status' => 'concluida',
                'opened_at' => Carbon::now()->subDays(25),
                'deadline_at' => Carbon::now()->subDays(4),
                'started_at' => Carbon::now()->subDays(20),
                'completed_at' => Carbon::now()->subDays(10),
                'response' => 'Dados do titular foram compilados e enviados por e-mail conforme solicitado. Incluídos: dados cadastrais, histórico de sessões e relatórios gerados.',
            ],
            [
                'type' => 'eliminacao',
                'requester_name' => 'João Pedro Oliveira',
                'requester_document' => '98765432100',
                'contact_method' => '(11) 99999-8888',
                'status' => 'em_andamento',
                'opened_at' => Carbon::now()->subDays(8),
                'deadline_at' => Carbon::now()->addDays(7),
                'started_at' => Carbon::now()->subDays(3),
            ],
            [
                'type' => 'retificacao',
                'requester_name' => 'Ana Carolina Ferreira',
                'requester_document' => '11122233344',
                'contact_method' => 'ana.ferreira@gmail.com',
                'status' => 'aberta',
                'opened_at' => Carbon::now()->subDays(2),
                'deadline_at' => Carbon::now()->addDays(13),
            ],
            [
                'type' => 'portabilidade',
                'requester_name' => 'Carlos Eduardo Lima',
                'requester_document' => '55566677788',
                'contact_method' => 'carlos.lima@outlook.com',
                'status' => 'vencida',
                'opened_at' => Carbon::now()->subDays(40),
                'deadline_at' => Carbon::now()->subDays(19),
            ],
            [
                'type' => 'revogacao',
                'requester_name' => 'Patrícia Mendes Costa',
                'requester_document' => '99988877766',
                'contact_method' => '(21) 98765-4321',
                'status' => 'aberta',
                'opened_at' => Carbon::now()->subDays(1),
                'deadline_at' => Carbon::now()->addDays(14),
            ],
        ];

        foreach ($requests as $data) {
            DataRequestModel::firstOrCreate(
                ['requester_document' => $data['requester_document'], 'type' => $data['type']],
                array_merge($data, [
                    'created_by' => $operator->id,
                    'assigned_operator_id' => in_array($data['status'], ['em_andamento', 'concluida']) ? $operator->id : null,
                ])
            );
        }

        $this->command->info('  ✓ ' . DataRequestModel::count() . ' requisições de direitos criadas');
    }

    private function seedAccessLogs($kids, User $operator): void
    {
        $operations = ['view', 'download_pdf', 'edit', 'view', 'view'];

        // Criar 20 logs de acesso distribuídos nos últimos 30 dias
        for ($i = 0; $i < 20; $i++) {
            $kid = $kids->random();
            AccessLogModel::create([
                'operator_id' => $operator->id,
                'medical_record_id' => $kid->id,
                'operation_type' => $operations[array_rand($operations)],
                'ip_address' => '192.168.1.' . rand(1, 254),
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/120.0.0.0',
                'accessed_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);
        }

        $this->command->info('  ✓ ' . AccessLogModel::count() . ' logs de acesso criados');
    }

    private function seedRetentionPolicies(User $operator): void
    {
        $policies = [
            [
                'category' => 'prontuarios',
                'retention_days' => 7300,
                'expiration_action' => 'sinalizar_revisao',
                'legal_minimum_days' => 7300,
                'legal_reference' => 'CFM Resolução 1.821/2007 — 20 anos',
            ],
            [
                'category' => 'consentimentos',
                'retention_days' => 1825,
                'expiration_action' => 'sinalizar_revisao',
                'legal_minimum_days' => 1825,
                'legal_reference' => 'LGPD Art. 16 — 5 anos após término do tratamento',
            ],
            [
                'category' => 'access_logs',
                'retention_days' => 1825,
                'expiration_action' => 'anonimizar',
                'legal_minimum_days' => 1825,
                'legal_reference' => 'Marco Civil da Internet Art. 15 — 5 anos',
            ],
            [
                'category' => 'dados_cadastrais',
                'retention_days' => 1825,
                'expiration_action' => 'sinalizar_revisao',
                'legal_minimum_days' => 1825,
                'legal_reference' => 'Código Civil Art. 206 §5º — prazo prescricional geral de 5 anos',
            ],
        ];

        foreach ($policies as $data) {
            RetentionPolicyModel::firstOrCreate(
                ['category' => $data['category']],
                array_merge($data, ['created_by' => $operator->id])
            );
        }

        $this->command->info('  ✓ ' . RetentionPolicyModel::count() . ' políticas de retenção criadas');
    }
}
