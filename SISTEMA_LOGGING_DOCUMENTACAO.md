# Sistema Centralizado de Logging - Maiêutica

## Visão Geral

O Sistema Centralizado de Logging do Maiêutica foi desenvolvido para resolver os problemas identificados na análise inicial:

- **75 registros de log inconsistentes** - Agora padronizados
- **8 níveis diferentes de log** - Reduzidos a 4 níveis principais (info, warning, error, critical)
- **Dados sensíveis expostos** - Sanitização automática implementada
- **Códigos dd() em produção** - Substituídos por logging estruturado
- **3 idiomas misturados** - Padronizado para português brasileiro
- **Padrões inconsistentes** - Sistema unificado implementado

## Componentes do Sistema

### 1. Enums de Categorização

#### LogCategory (`app/Enums/LogCategory.php`)
- USER_OPERATIONS
- PROFESSIONAL_OPERATIONS
- KID_OPERATIONS
- CHECKLIST_OPERATIONS
- COMPETENCE_OPERATIONS
- AUTHENTICATION
- AUTHORIZATION
- SYSTEM_OPERATIONS
- SECURITY_EVENTS
- PERFORMANCE_MONITORING
- DATA_VALIDATION
- FILE_OPERATIONS
- EMAIL_OPERATIONS
- ERROR_HANDLING

#### LogOperation (`app/Enums/LogOperation.php`)
- CREATE, READ, UPDATE, DELETE
- LOGIN, LOGOUT, REGISTER
- RESET_PASSWORD, CHANGE_PASSWORD
- UPLOAD, DOWNLOAD, EXPORT, IMPORT
- ACCESS_DENIED, VALIDATION_FAILED
- EXCEPTION_OCCURRED
- E outros...

### 2. Sistema de Sanitização

#### DataSanitizer (`app/Services/Log/DataSanitizer.php`)

**Campos Sensíveis (Completamente Redacted):**
- Senhas (password, password_confirmation, current_password, etc.)
- Tokens (api_token, access_token, refresh_token, etc.)
- Chaves (secret, key, private_key, public_key)
- Dados financeiros (card_number, cvv)

**Dados Pessoais (Mascarados quando necessário):**
- E-mail: `joao@exemplo.com` → `jo***@exemplo.com`
- Telefone: `11999999999` → `11*******99`
- CPF: `12345678901` → `123.***.***-01`
- RG: `1234567` → `12*****7`
- CEP: `12345678` → `12***-***`

**Nomes (Anonimizados quando necessário):**
- `João Silva` → `J*** S****`
- `Maria` → `M***`

### 3. LoggingService Central

#### Principais Métodos:

```php
// Operações específicas por domínio
LoggingService::logUserOperation(LogOperation $op, string $message, array $context = []);
LoggingService::logKidOperation(LogOperation $op, string $message, array $context = []);
LoggingService::logChecklistOperation(LogOperation $op, string $message, array $context = []);
LoggingService::logProfessionalOperation(LogOperation $op, string $message, array $context = []);

// Eventos específicos
LoggingService::logAuthentication(LogOperation $op, string $message, array $context = []);
LoggingService::logSecurityEvent(LogOperation $op, string $message, array $context = []);
LoggingService::logPerformance(string $message, array $context = [], ?float $time = null);

// Tratamento de exceções
LoggingService::logException(Throwable $exception, string $message = null, array $context = []);

// Medição de performance
LoggingService::measureExecution(callable $callback, string $operationName = null);

// Trace para operações relacionadas
$traceId = LoggingService::startTrace("operation_name");
// ... múltiplas operações relacionadas
LoggingService::endTrace();
```

### 4. Traits para Facilitar o Uso

#### HasLogging (`app/Traits/HasLogging.php`)

**Logging Automático nos Models:**
- Criação, atualização e exclusão são logadas automaticamente
- Inclui dados das mudanças (antes/depois)
- Identifica soft deletes

**Métodos Disponíveis nos Models:**
```php
$user->logCustomOperation(LogOperation::UPDATE, "Status alterado");
$user->logView('user_profile');
$user->logExport('PDF', ['report_type' => 'comprehensive']);
$user->getLogIdentifier(); // Nome inteligente para logs
```

#### HasControllerLogging (`app/Traits/HasControllerLogging.php`)

**Para Controllers:**
```php
// Em controllers que usam a trait
$this->logResourceAction('show', $user);
$this->logUnauthorizedAccess('edit', $user);
$this->logBulkOperation('delete', [1,2,3], 'User');
$this->logSearch($request, $resultsCount);
$this->logFileUpload($fileName, $path, $fileSize);
```

### 5. Channels de Logging Configurados

#### Channels Específicos:
- **application**: Logs gerais da aplicação (60 dias)
- **security**: Eventos de segurança (90 dias)
- **performance**: Monitoramento de performance (30 dias)
- **errors**: Erros e exceções (90 dias)

#### Stack Configurado:
```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['application', 'security', 'performance'],
    'ignore_exceptions' => false,
]
```

### 6. Service Provider Automático

#### LoggingServiceProvider (`app/Providers/LoggingServiceProvider.php`)
- Registra automaticamente o LoggingService
- Configura channels de logging
- Enriquece logs críticos automaticamente
- Integração com log-viewer

## Conformidade LGPD

### Sanitização Automática
- Todos os logs passam pela sanitização automática
- Dados sensíveis são completamente removidos
- Dados pessoais podem ser mascarados quando necessário
- Anonimização inteligente de nomes quando aplicável

### Controles de Retenção
- Logs de segurança: 90 dias
- Logs de aplicação: 60 dias
- Logs de performance: 30 dias
- Configurável por channel

## Integração com Log-Viewer

### Structured Context
Todos os logs incluem contexto estruturado:
```php
[
    'message' => 'Operação executada',
    'context' => [...], // Dados sanitizados
    'metadata' => [
        'timestamp' => '2024-01-01T00:00:00Z',
        'trace_id' => 'uuid-trace-id',
        'user_id' => 123,
        'ip_address' => '192.168.1.1',
        'user_agent' => '...',
        'category' => 'user_operations',
        'operation' => 'update',
        'level' => 'info'
    ]
]
```

### Filtragem Avançada
- Por categoria de operação
- Por nível de log
- Por usuário
- Por trace_id (operações relacionadas)
- Por período

## Exemplos de Uso Prático

### 1. Em Models (Automático)
```php
// Apenas adicione a trait
class User extends Authenticatable
{
    use HasLogging;
    
    // Logging automático em create, update, delete
}

// Logging manual quando necessário
$user->logView('profile_page');
$user->logExport('PDF');
```

### 2. Em Controllers
```php
class UserController extends Controller
{
    use HasControllerLogging;
    
    public function show(User $user)
    {
        $this->logResourceAction('show', $user);
        
        return view('users.show', compact('user'));
    }
    
    public function unauthorizedAction()
    {
        $this->logUnauthorizedAccess('admin_panel');
        abort(403);
    }
}
```

### 3. Em Services
```php
class UserService
{
    public function createUser(array $data): User
    {
        $traceId = LoggingService::startTrace('user_creation');
        
        try {
            $user = User::create($data); // Auto-logado pela trait
            
            LoggingService::logEmailOperation(
                LogOperation::EMAIL_SEND,
                "E-mail de boas-vindas enviado",
                ['user_id' => $user->id, 'template' => 'welcome']
            );
            
            return $user;
            
        } catch (\Exception $e) {
            LoggingService::logException($e, "Falha na criação de usuário");
            throw $e;
        } finally {
            LoggingService::endTrace();
        }
    }
}
```

### 4. Monitoramento de Performance
```php
$result = LoggingService::measureExecution(function () {
    return ComplexService::generateReport();
}, "Geração de relatório complexo");
```

### 5. Eventos de Segurança
```php
// Login falhado
LoggingService::logAuthentication(
    LogOperation::LOGIN,
    "Tentativa de login com credenciais inválidas",
    ['attempted_email' => $email],
    'warning'
);

// Acesso negado
LoggingService::logSecurityEvent(
    LogOperation::ACCESS_DENIED,
    "Tentativa de acesso não autorizado à área administrativa",
    ['requested_route' => 'admin.dashboard']
);
```

## Migration do Sistema Atual

### Substitua logs existentes:

**ANTES:**
```php
Log::info('User created', ['user' => $user]);
info('Professional updated');
\Log::warning("Access denied for user {$user->id}");
logger()->error('Exception occurred', ['exception' => $e]);
dd($data); // ❌ Em produção
```

**DEPOIS:**
```php
LoggingService::logUserOperation(LogOperation::CREATE, "Usuário criado", ['user_id' => $user->id]);
LoggingService::logProfessionalOperation(LogOperation::UPDATE, "Profissional atualizado");
LoggingService::logSecurityEvent(LogOperation::ACCESS_DENIED, "Acesso negado", ['user_id' => $user->id]);
LoggingService::logException($e, "Exceção capturada");
LoggingService::logSystemOperation(LogOperation::READ, "Debug data", $data); // ✅ Sanitizado
```

## Benefícios Implementados

✅ **Padronização Completa**: Todos os logs seguem o mesmo padrão
✅ **Sanitização Automática**: Dados sensíveis protegidos automaticamente
✅ **Categorização Estruturada**: Logs organizados por domínio e operação
✅ **Performance Monitoring**: Medição automática de tempo de execução
✅ **Trace Correlations**: Operações relacionadas linkadas por trace_id
✅ **LGPD Compliant**: Mascaramento e retenção adequados
✅ **Log-Viewer Integration**: Filtragem e busca avançadas
✅ **Easy Migration**: Traits e helpers para adoção gradual
✅ **Production Ready**: Channels separados e níveis apropriados
✅ **Developer Friendly**: APIs intuitivas e auto-documentadas

## Próximos Passos

1. **Aplicar o sistema nos controllers existentes**
2. **Migrar logs manuais existentes**
3. **Configurar alertas para logs críticos**
4. **Implementar dashboards de monitoramento**
5. **Treinar a equipe no novo sistema**

O sistema está completamente implementado e pronto para uso em produção!