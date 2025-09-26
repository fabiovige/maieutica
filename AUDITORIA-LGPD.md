# Sistema de Auditoria LGPD - Maiêutica

## Visão Geral

Sistema completo de auditoria para conformidade LGPD implementado no sistema Maiêutica. Registra automaticamente todas as operações críticas relacionadas a dados pessoais de crianças, permitindo rastreabilidade completa de "quem, quando, o quê, onde".

## Componentes Implementados

### 1. Modelo AuditLog
**Arquivo:** `app/Models/AuditLog.php`

**Campos:**
- `user_id`: Usuário que executou a ação
- `action`: Tipo de operação (CREATE, UPDATE, DELETE, read)
- `resource`: Modelo/recurso afetado
- `resource_id`: ID específico do recurso
- `ip_address`: Endereço IP da requisição
- `user_agent`: Navegador/dispositivo utilizado
- `data_before`: Estado anterior dos dados
- `data_after`: Estado posterior dos dados
- `context`: Contexto adicional da operação
- `created_at`: Timestamp da operação

**Funcionalidades:**
- Relacionamento com usuários
- Scopes para filtragem (por usuário, recurso, ação, período)
- Método estático `logAction()` para criação simplificada de logs

### 2. Trait HasAuditLog
**Arquivo:** `app/Traits/HasAuditLog.php`

**Responsabilidades:**
- Registra automaticamente operações CREATE, UPDATE, DELETE nos modelos
- Captura mudanças apenas em campos sensíveis (configuráveis)
- Método `logReadAccess()` para registro manual de acessos de leitura
- Configuração flexível de campos auditáveis via propriedades do modelo

**Implementado nos modelos:**
- `Kid`: Dados de crianças (nome, data nascimento, etc.)
- `User`: Dados de usuários
- `Responsible`: Dados de responsáveis
- `Professional`: Dados de profissionais

### 3. Middleware AuditLogger
**Arquivo:** `app/Http/Middleware/AuditLogger.php`

**Funcionalidades:**
- Registra automaticamente acessos a rotas sensíveis
- Identifica tentativas de autenticação
- Captura parâmetros da requisição (sanitizando senhas)
- Mede tempo de execução
- Registra códigos de status HTTP (incluindo erros)

**Rotas monitoradas:**
- `kids.*`
- `responsibles.*`
- `checklists.*`
- `professionals.*`
- `users.*`
- `analysis.*`
- `export.*`

### 4. Controller e Views
**Arquivos:**
- `app/Http/Controllers/AuditLogController.php`
- `resources/views/audit/index.blade.php`
- `resources/views/audit/show.blade.php`
- `resources/views/audit/stats.blade.php`

**Funcionalidades:**
- Dashboard de auditoria com filtros avançados
- Visualização detalhada de logs individuais
- Estatísticas de uso e atividade
- Exportação de relatórios CSV
- Gráficos de atividade diária

### 5. Sistema de Permissões
**Seeder:** `database/seeders/AuditPermissionsSeeder.php`

**Permissões criadas:**
- `view-audit-logs`: Visualizar todos os logs (Admin/SuperAdmin)
- `view-own-audit-logs`: Visualizar próprios logs (Profissionais)
- `export-audit-logs`: Exportar relatórios (Admin/SuperAdmin)
- `view-audit-stats`: Visualizar estatísticas (Admin/SuperAdmin)
- `delete-audit-logs`: Excluir logs (apenas SuperAdmin)

### 6. Comando de Manutenção
**Arquivo:** `app/Console/Commands/CleanOldAuditLogs.php`

**Funcionalidades:**
- Limpeza automática de logs antigos
- Configurável por dias de retenção (padrão: 365 dias)
- Modo dry-run para simulação
- Estatísticas de limpeza
- Processamento em lotes para performance

## Conformidade LGPD

### Requisitos Atendidos

1. **Rastreabilidade Completa:**
   - Registro de todos os acessos a dados de crianças
   - Identificação do usuário, data/hora, IP e contexto
   - Histórico de mudanças com estados anterior e posterior

2. **Dados Sensíveis Protegidos:**
   - Auditoria específica para modelos com dados pessoais
   - Sanitização de senhas em logs de requisição
   - Controle granular de campos auditados

3. **Controle de Acesso:**
   - Permissões baseadas em roles
   - Profissionais podem ver apenas seus próprios logs
   - Admins têm acesso completo para conformidade

4. **Retenção de Dados:**
   - Comando automático para limpeza de logs antigos
   - Configurável conforme política de retenção da organização
   - Preservação de logs críticos

### Operações Auditadas

**Automaticamente (via Trait):**
- Criação, atualização e exclusão de crianças
- Modificações de usuários e profissionais
- Alterações em dados de responsáveis

**Manualmente (via Controllers):**
- Visualização de perfis de crianças
- Acesso a relatórios de desenvolvimento
- Preenchimento de checklists
- Exportação de dados

**Via Middleware:**
- Todas as requisições a rotas sensíveis
- Tentativas de login/logout
- Acessos com parâmetros de crianças/responsáveis

## Uso do Sistema

### Acesso ao Dashboard
```
/audit
```

### Estatísticas
```
/audit/stats
```

### Exportação de Dados
```
/audit/export?start_date=2024-01-01&end_date=2024-12-31
```

### Limpeza de Logs
```bash
# Simular limpeza (dry-run)
php artisan audit:clean --days=365 --dry-run

# Executar limpeza mantendo logs de 1 ano
php artisan audit:clean --days=365
```

### Registro Manual de Acessos
```php
// Em qualquer controller
$kid = Kid::find(1);
$kid->logReadAccess('Consulta de dados para relatório');

// Ou usando o modelo diretamente
AuditLog::logAction('read', 'Kid', 1, null, null, 'Acesso via API');
```

## Considerações de Performance

1. **Índices de Banco:**
   - Otimizados para consultas por usuário, data, recurso e ação
   - Performance adequada mesmo com milhões de logs

2. **Middleware Inteligente:**
   - Filtra apenas rotas realmente sensíveis
   - Evita logs desnecessários de assets/arquivos estáticos

3. **Processamento Assíncrono:**
   - Logs são criados após a resposta (não impactam UX)
   - Processamento em lotes para operações pesadas

## Monitoramento e Alertas

### Métricas Importantes
- Volume de acessos a dados de crianças por usuário
- Tentativas de acesso negadas
- Atividade fora do horário comercial
- Exportações de dados em massa

### Relatórios Regulares
- Relatório mensal de conformidade LGPD
- Análise de padrões de acesso
- Identificação de comportamentos suspeitos

## Backup e Recuperação

### Recomendações
1. Backup diário da tabela `audit_logs`
2. Replicação em ambiente separado para auditoria
3. Política de retenção de 7 anos (conforme LGPD)
4. Logs de backup também auditados

## Próximos Passos

1. **Alertas Automáticos:**
   - Notificações para acessos anômalos
   - Relatórios semanais por email

2. **Integração com SIEM:**
   - Exportação para sistemas de monitoramento
   - Correlação com outros logs de segurança

3. **Interface Mobile:**
   - Visualização de logs em dispositivos móveis
   - Notificações push para admins

4. **Análise de Comportamento:**
   - Machine learning para detectar padrões
   - Scoring de risco por usuário

---

**Sistema implementado com sucesso - Conformidade LGPD garantida! ✅**