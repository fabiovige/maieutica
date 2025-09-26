# Sistema de Criptografia LGPD - Maiêutica

Este documento descreve o sistema de criptografia implementado para conformidade com a LGPD (Lei Geral de Proteção de Dados).

## Visão Geral

O sistema implementa criptografia automática para dados sensíveis de crianças, responsáveis e usuários, utilizando o sistema de criptografia nativo do Laravel para máxima segurança e compatibilidade.

## Características Principais

### 🔐 Criptografia Transparente
- Criptografia/descriptografia automática sem mudanças no código existente
- Utiliza Laravel Crypt (AES-256-CBC) para máxima segurança
- Fallback gracioso para dados não criptografados (compatibilidade com migração)

### 🛡️ Conformidade LGPD
- Proteção de dados pessoais de crianças (Art. 14 LGPD)
- Criptografia de dados identificadores
- Logs de tentativas de descriptografia falhadas para auditoria

### 📊 Impacto Mínimo na Performance
- Criptografia apenas nos campos definidos
- Cache interno para evitar re-descriptografia
- Queries otimizadas com método whereEncrypted()

## Campos Criptografados

### Modelo Kid (Dados de Crianças)
- `name` - Nome completo da criança

### Modelo Responsible (Dados dos Responsáveis)
- `name` - Nome completo
- `email` - Email pessoal
- `cpf` - Documento de identificação
- `cell` - Número de celular
- `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade` - Endereço completo

### Modelo User (Dados dos Usuários)
- `name` - Nome completo
- `phone` - Telefone
- `postal_code`, `street`, `number`, `complement`, `neighborhood`, `city` - Endereço completo

## Implementação Técnica

### Trait EncryptedAttributes
```php
use App\Traits\EncryptedAttributes;

class Kid extends BaseModel
{
    use EncryptedAttributes;

    protected function getEncryptedFields(): array
    {
        return ['name'];
    }
}
```

### Métodos Principais

#### Verificação de Criptografia
```php
$kid = Kid::find(1);
$encrypted = $kid->isFieldEncrypted('name'); // true/false
```

#### Busca em Campos Criptografados
```php
$kids = Kid::whereEncrypted('name', 'João Silva')->get();
```

#### Acesso a Dados Crus Criptografados
```php
$encryptedRaw = $kid->getEncryptedRawAttribute('name');
```

#### Forçar Descriptografia
```php
$decrypted = $kid->forceDecrypt('name');
```

## Migração de Dados Existentes

### Comando de Migração
```bash
# Migrar todos os modelos
php artisan encrypt:existing-data --backup

# Migrar modelo específico
php artisan encrypt:existing-data --model=Kid --force

# Ver opções
php artisan encrypt:existing-data --help
```

### Processo de Migração Seguro
1. **Backup Automático**: Criação de backup antes da migração
2. **Verificação de Integridade**: Validação dos dados antes e após criptografia
3. **Rollback**: Possibilidade de reverter alterações
4. **Progresso Visual**: Barra de progresso e estatísticas detalhadas

## Estrutura do Banco de Dados

### Alterações nas Colunas
As colunas de campos sensíveis foram expandidas para VARCHAR(1000) para acomodar os dados criptografados:

```sql
-- Migração aplicada
ALTER TABLE kids MODIFY name VARCHAR(1000);
ALTER TABLE responsibles MODIFY name VARCHAR(1000), email VARCHAR(1000);
ALTER TABLE users MODIFY name VARCHAR(1000), phone VARCHAR(1000);
-- ... demais campos
```

## Segurança e Monitoramento

### Logs de Segurança
- Tentativas de descriptografia falhadas são logadas
- Erros de criptografia são registrados para auditoria
- Identificação de potenciais tentativas de acesso não autorizado

### Tratamento de Erros
```php
// Descriptografia falha graciosamente
try {
    $decryptedValue = $model->field_name;
} catch (DecryptException $e) {
    // Retorna valor original, loga o erro
    Log::warning('Decryption failed', ['model' => get_class($model)]);
    return $rawValue;
}
```

### Validação de Integridade
```php
// Verificar se campo está devidamente criptografado
$isEncrypted = $model->isFieldEncrypted('field_name');

// Verificar integridade dos dados
$rawValue = $model->getEncryptedRawAttribute('field_name');
$decryptedValue = Crypt::decrypt($rawValue); // Lança exceção se inválido
```

## Comandos Administrativos

### Verificar Status da Criptografia
```bash
# Verificar campos criptografados por modelo
php artisan tinker
> $kid = App\Models\Kid::first();
> $kid->getEncryptedFields();
> $kid->isFieldEncrypted('name');
```

### Descriptografar para Auditoria (Emergência)
```php
// Em caso de necessidade de acesso direto aos dados
$kid = Kid::find(1);
$plainName = $kid->forceDecrypt('name');
```

## Compliance LGPD

### Artigos Atendidos
- **Art. 14**: Proteção de dados pessoais de crianças
- **Art. 46**: Medidas técnicas para proteção de dados
- **Art. 49**: Anonimização como forma de proteção

### Benefícios para Compliance
1. **Minimização**: Apenas campos necessários são criptografados
2. **Segurança**: Dados protegidos mesmo em caso de vazamento do banco
3. **Transparência**: Sistema auditável e documentado
4. **Integridade**: Verificação automática da validade dos dados

## Manutenção e Monitoramento

### Rotinas Recomendadas
1. **Backup Regular**: Backup completo antes de grandes alterações
2. **Teste de Integridade**: Verificação periódica da descriptografia
3. **Log Monitoring**: Monitoramento de logs de erro de criptografia
4. **Auditoria**: Revisão periódica dos campos criptografados

### Troubleshooting

#### "Data too long for column"
```bash
# Executar migração para aumentar colunas
php artisan migrate
```

#### "The payload is invalid"
```bash
# Dado possivelmente corrompido - verificar integridade
php artisan encrypt:existing-data --model=Kid --force
```

#### Performance Degradada
```php
// Otimizar queries evitando N+1
Kid::with(['responsible', 'professionals'])->get();
```

## Próximos Passos

### Melhorias Futuras
1. **Criptografia de Chaves Múltiplas**: Rotação de chaves de criptografia
2. **Auditoria Avançada**: Log detalhado de acesso aos dados sensíveis
3. **Anonimização Automática**: Processo automático para dados antigos
4. **API Segura**: Endpoints específicos para dados criptografados

### Monitoramento Contínuo
- Alertas para falhas de descriptografia
- Métricas de performance da criptografia
- Relatórios de compliance automáticos

---

**Implementado com segurança e conformidade LGPD em mente.**
**Sistema testado e validado para ambiente de produção.**

*Documentação atualizada em: ${new Date().toLocaleDateString('pt-BR')}*