# Módulo LGPD — Teste Local

## Pré-requisitos

- Container Docker rodando (`maieutica_app`)
- Migrations do módulo LGPD já executadas
- Permissões LGPD já registradas (seeder de permissões)
- Usuário admin com permissões LGPD atribuídas

## Popular dados de demonstração

```bash
docker exec -it maieutica_app php artisan db:seed --class="App\Modules\Lgpd\Infrastructure\Seeders\LgpdDemoDataSeeder"
```

Esse comando cria:
- **4 consentimentos** — 3 ativos + 1 revogado, com diferentes bases legais
- **5 requisições de direitos** — uma de cada status (aberta, em_andamento, concluída, vencida) e tipos variados
- **20 logs de acesso** — distribuídos nos últimos 30 dias com operações variadas (view, download_pdf, edit)
- **4 políticas de retenção** — uma por categoria (prontuários, consentimentos, access_logs, dados_cadastrais)

> O seeder usa `firstOrCreate` para consentimentos, requisições e políticas — pode rodar múltiplas vezes sem duplicar. Os access logs sempre criam novos registros.

## URLs para testar

| Tela | URL | O que verificar |
|------|-----|-----------------|
| Consentimentos | http://maieutica.test/lgpd/consents | DataTable com 4 registros, filtros, botão revogar |
| Detalhes consentimento | http://maieutica.test/lgpd/consents/1 | Card com dados, histórico de base legal |
| Requisições | http://maieutica.test/lgpd/requests | DataTable com 5 registros, badges de status coloridos |
| Detalhes requisição | http://maieutica.test/lgpd/requests/1 | Botões de ação conforme status |
| Logs de Acesso | http://maieutica.test/lgpd/access-logs | DataTable com 20+ registros, filtros por período |
| Retenção de Dados | http://maieutica.test/lgpd/retention-policies | Tabela com 4 políticas, formulário de criação |
| Relatório PDF | http://maieutica.test/lgpd/reports/compliance | Formulário de período, gera PDF com download |

## Testar o relatório PDF

1. Acesse http://maieutica.test/lgpd/reports/compliance
2. Selecione data inicial: 30 dias atrás
3. Selecione data final: hoje
4. Clique em "Gerar Relatório PDF"
5. Um PDF deve ser baixado com métricas do período

## Testar fluxo de requisição de direitos

1. Acesse http://maieutica.test/lgpd/requests
2. Clique em uma requisição com status "Aberta" (ex: Ana Carolina Ferreira)
3. Clique em "Assumir Requisição" → status muda para "Em andamento"
4. Preencha a resposta e clique em "Concluir Requisição" → status muda para "Concluída"

## Testar revogação de consentimento

1. Acesse http://maieutica.test/lgpd/consents
2. Encontre um consentimento com status "Ativo"
3. Clique no botão de revogar (ícone X vermelho)
4. Confirme → status muda para "Revogado"

## Testar o Job de verificação de prazos

```bash
docker exec -it maieutica_app php artisan schedule:run
```

Ou executar o job diretamente:

```bash
docker exec -it maieutica_app php artisan tinker --execute="dispatch(new \App\Modules\Lgpd\Jobs\CheckDataRequestDeadlinesJob());"
```

Verifique o log em `storage/logs/laravel.log` — deve aparecer:
```
[LGPD] CheckDataRequestDeadlinesJob executado com sucesso
```

## Limpar dados de demonstração (se necessário)

```bash
docker exec -it maieutica_app php artisan tinker --execute="
\App\Modules\Lgpd\Infrastructure\Models\AccessLogModel::truncate();
\App\Modules\Lgpd\Infrastructure\Models\DataRequestModel::truncate();
\App\Modules\Lgpd\Infrastructure\Models\ConsentRecordModel::truncate();
\App\Modules\Lgpd\Infrastructure\Models\RetentionPolicyModel::truncate();
DB::table('lgpd_consent_legal_basis_history')->truncate();
echo 'Dados LGPD limpos.';
"
```

## Atribuir permissões LGPD a outro usuário

```bash
docker exec -it maieutica_app php artisan tinker --execute="
\$user = \App\Models\User::find(ID_DO_USUARIO);
\$user->givePermissionTo([
    'lgpd-consent-manage', 'lgpd-consent-list', 'lgpd-consent-show',
    'lgpd-access-log-view',
    'lgpd-request-manage', 'lgpd-request-list', 'lgpd-request-show',
    'lgpd-report-generate',
    'lgpd-retention-manage', 'lgpd-retention-list',
]);
echo 'Permissões atribuídas a ' . \$user->name;
"
```
