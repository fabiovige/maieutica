# Middleware de Sanitização de Input - Proteção XSS

## Visão Geral

O middleware `SanitizeInput` fornece proteção automática contra ataques XSS (Cross-Site Scripting) no sistema Maiêutica, sanitizando todos os inputs de usuário de forma inteligente e configurável.

## Funcionalidades Principais

### 1. **Sanitização Automática**
- Remove automaticamente scripts maliciosos e tags HTML perigosas
- Preserva HTML seguro em campos de rich text
- Escapa caracteres especiais em campos de texto simples

### 2. **Sanitização Inteligente**
- **Campos de texto simples**: HTML completamente escapado
- **Campos rich text**: HTML seguro preservado (p, strong, em, ul, ol, li, etc.)
- **Campos sensíveis**: Não sanitizados (passwords, tokens)

### 3. **Detecção e Monitoramento**
- Detecta tentativas de XSS usando patterns avançados
- Registra tentativas de ataques no log do sistema
- Inclui informações de contexto (IP, user agent, URL, usuário)

### 4. **Performance Otimizada**
- Cache de conteúdo sanitizado com HTMLPurifier
- Processamento apenas quando necessário
- Não processa uploads de arquivos ou requisições GET

## Configuração

### Arquivo: `config/sanitize.php`

```php
return [
    // Habilitar/desabilitar o middleware
    'enabled' => env('SANITIZE_INPUT_ENABLED', true),

    // Registrar tentativas de XSS
    'log_xss_attempts' => env('SANITIZE_LOG_XSS', true),

    // Tags HTML permitidas em campos rich text
    'allowed_tags' => [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote',
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'width', 'height', 'title'],
    ],

    // Campos tratados como rich text
    'rich_text_fields' => [
        'description', 'content', 'details', 'note', 'notes',
        'observation', 'comment', 'bio', 'message', 'body',
    ],

    // Campos que não devem ser sanitizados
    'skip_fields' => [
        '_token', '_method', 'password', 'password_confirmation',
    ],

    // Rotas que devem ser ignoradas
    'skip_routes' => [
        'api/*', 'upload/*', 'files/*', 'storage/*',
    ],
];
```

### Variáveis de Ambiente

```env
# .env
SANITIZE_INPUT_ENABLED=true
SANITIZE_LOG_XSS=true
```

## Tipos de Proteção

### 1. **Script Injection**
```php
// Input: <script>alert('XSS')</script>João
// Output: &lt;script&gt;alert('XSS')&lt;/script&gt;João
```

### 2. **Event Handlers**
```php
// Input: <img src="x" onerror="alert('XSS')">
// Output: <img src="x" alt="" /> (em rich text)
```

### 3. **JavaScript Protocols**
```php
// Input: javascript:alert('XSS')
// Output: alert('XSS')
```

### 4. **Form Injection**
```php
// Input: </textarea><form><input type="password">
// Output: Tags form removidas completamente
```

## Campos por Tipo

### Texto Simples (HTML Escapado)
- `name`, `email`, `title`, `phone`
- Todo HTML é convertido em entidades

### Rich Text (HTML Seguro)
- `description`, `note`, `content`, `details`
- Preserva tags seguras, remove perigosas
- Patterns: `*_description`, `content_*`, etc.

### Não Sanitizados
- `password`, `current_password`, `_token`, `_method`
- Campos que começam com `_` ou `csrf`

## Monitoramento

### Logs de Segurança

Tentativas de XSS são registradas em `storage/logs/laravel.log`:

```json
{
  "message": "Potencial tentativa de XSS detectada",
  "field": "message",
  "pattern_matched": "/<script[^>]*>.*?<\\/script>/is",
  "user_agent": "Mozilla/5.0...",
  "ip": "192.168.1.100",
  "url": "/kids/create",
  "user_id": 123,
  "suspicious_content": "<script>alert('XSS attempt')</script>...",
  "timestamp": "2025-09-26T09:00:00.000Z"
}
```

### Patterns de Detecção

O sistema detecta os seguintes patterns maliciosos:
- Tags `<script>`
- Protocolos `javascript:`, `vbscript:`
- Event handlers (`onclick`, `onerror`, etc.)
- Tags perigosas (`<iframe>`, `<object>`, `<form>`)
- Expressões CSS maliciosas
- Funções JavaScript (`eval`, `document.`, `window.`)

## Comandos Artisan

### Testar Sanitização

```bash
# Executar testes de demonstração
php artisan security:test-sanitize

# Ver exemplos detalhados
php artisan security:test-sanitize --examples
```

### Executar Testes

```bash
# Testes unitários do middleware
./vendor/bin/phpunit tests/Feature/SanitizeInputMiddlewareTest.php
```

## Integração

### Middleware Pipeline

O middleware está integrado no grupo 'web' no arquivo `app/Http/Kernel.php`:

```php
'web' => [
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    AuthenticateSession::class,
    ShareErrorsFromSession::class,
    VerifyCsrfToken::class,
    SanitizeInput::class,        // ← Posicionado após CSRF
    SubstituteBindings::class,
    // ...
],
```

### Dependências

- **ezyang/htmlpurifier**: Sanitização robusta de HTML
- **Laravel Cache**: Cache de conteúdo sanitizado
- **Laravel Log**: Registro de tentativas de XSS

## Personalização

### Adicionar Campos Rich Text

```php
// config/sanitize.php
'rich_text_fields' => [
    'description',
    'custom_field',           // Adicionar novo campo
    'product_description',    // Mais um campo
],
```

### Personalizar Tags Permitidas

```php
// config/sanitize.php
'allowed_tags' => [
    'p', 'br', 'strong',
    'table' => ['class'],     // Adicionar table com atributo class
    'td', 'tr', 'th',        // Células de tabela
],
```

### Adicionar Patterns XSS

```php
// config/sanitize.php
'xss_patterns' => [
    '/<script[^>]*>.*?<\/script>/is',
    '/data:text\/html/i',          // Novo pattern para data URLs
    '/style\s*=.*expression/i',    // CSS expressions
],
```

## Considerações de Performance

### Cache
- Conteúdo sanitizado é cacheado por 1 hora
- Cache baseado no hash MD5 do conteúdo original
- Armazenado em `storage/framework/cache/`

### Otimizações
- HTMLPurifier inicializado sob demanda
- Configuração carregada apenas uma vez por request
- Processamento apenas em POST/PUT/PATCH
- Pula automaticamente uploads de arquivos

### Diretrizes
- Cache do HTMLPurifier em `storage/framework/cache/htmlpurifier/`
- Limpeza automática de cache antigo
- Configuração otimizada para UTF-8

## Troubleshooting

### Problema: Conteúdo sendo removido incorretamente
**Solução**: Verificar se o campo está na lista `rich_text_fields`

### Problema: Performance lenta
**Solução**: Verificar cache do HTMLPurifier e configuração de patterns

### Problema: Logs excessivos
**Solução**: Desabilitar `log_xss_attempts` ou ajustar patterns

### Problema: Funcionalidade quebrada
**Solução**: Adicionar rota ou campo às listas de skip

## Segurança

### Princípios Aplicados
- **Defense in Depth**: Múltiplas camadas de proteção
- **Whitelist Approach**: Apenas tags/atributos seguros permitidos
- **Context Awareness**: Diferentes sanitizações para diferentes contextos
- **Logging & Monitoring**: Visibilidade completa de tentativas de ataque

### Recomendações
- Manter HTMLPurifier atualizado
- Revisar regularmente patterns de detecção
- Monitorar logs de tentativas de XSS
- Testar após mudanças de configuração

## Compatibilidade

- **Laravel 9+**
- **PHP 8.1+**
- **HTMLPurifier 4.18+**
- **Compatível com todas as funcionalidades existentes do Maiêutica**

---

*Implementado em setembro de 2025 para fortalecer a segurança do sistema Maiêutica contra ataques XSS.*