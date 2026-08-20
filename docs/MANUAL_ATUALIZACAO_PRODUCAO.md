# Manual de Atualização em Produção

> **Processo atual de deploy:** a publicação normal é disparada pelo webhook
> GitHub -> Hostinger após o merge de um Pull Request na branch `main`. Não use
> `git pull` manual como procedimento rotineiro. Os comandos manuais deste
> documento servem para diagnóstico, configuração inicial ou recuperação quando
> o deploy automático não executar uma etapa. Para a funcionalidade de
> agendamentos, siga também `docs/orientacao-hostinger.md`.

## 📋 Checklist Pré-Deploy

Antes de iniciar qualquer atualização em produção, verifique:

- [ ] Todas as alterações foram testadas em ambiente de desenvolvimento
- [ ] Não há erros de sintaxe ou testes falhando
- [ ] Commits estão na branch correta (main/master)
- [ ] Changelog ou descrição das mudanças está documentada
- [ ] Você tem acesso SSH ao servidor de produção
- [ ] Você tem credenciais de backup do banco de dados
- [ ] Sistema está em horário de baixo tráfego (opcional, mas recomendado)

---

## 🔄 Procedimento Completo de Atualização

### 1. Backup Completo

⚠️ **CRÍTICO: SEMPRE faça backup antes de qualquer alteração!**

```bash
# Entrar no servidor de produção
ssh usuario@maieuticavaliacom.br

# Navegar até o diretório do projeto
cd /caminho/do/projeto

# 1.1 Backup do Banco de Dados
mysqldump -u usuario_db -p nome_banco > backup_$(date +%Y%m%d_%H%M%S).sql

# 1.2 Backup dos arquivos (opcional, mas recomendado)
tar -czf backup_files_$(date +%Y%m%d_%H%M%S).tar.gz \
    storage/ \
    public/images/ \
    .env

# 1.3 Verificar se os backups foram criados
ls -lh backup_*
```

### 2. Ativar Modo de Manutenção

```bash
# Colocar aplicação em modo de manutenção
php artisan down --message="Sistema em manutenção. Voltamos em breve!" --retry=60
```

### 3. Atualizar Código-Fonte

```bash
# 3.1 Verificar branch atual
git branch

# 3.2 Fazer stash de alterações locais (se houver)
git stash

# 3.3 Puxar últimas atualizações
git pull origin main

# OU se usar develop em produção
git pull origin develop

# 3.4 Verificar se pull foi bem-sucedido
git log -1
```

### 4. Atualizar Dependências

```bash
# 4.1 Atualizar dependências do Composer (backend)
composer install --no-dev --optimize-autoloader

# 4.2 Atualizar dependências do NPM (frontend)
npm ci --production

# 4.3 Compilar assets para produção
npm run production
```

### 5. Executar Migrações e Seeders

```bash
# 5.1 Verificar status das migrações (sem executar)
php artisan migrate:status

# 5.2 Executar migrações pendentes (se houver)
php artisan migrate --force

# 5.3 Executar seeder específico (exemplo: permissões)
php artisan db:seed --class=RoleAndPermissionSeeder --force
```

⚠️ **Notas importantes sobre seeders:**
- Use `--force` em produção para não pedir confirmação
- Seeders devem usar `firstOrCreate()` ou `updateOrCreate()` para evitar duplicação
- Nunca rode `db:seed` sem classe específica em produção (pode duplicar dados!)

### 6. Limpar e Otimizar Cache

```bash
# 6.1 Limpar todos os caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# OU usar o comando customizado (se disponível)
composer clear

# 6.2 Recriar caches otimizados para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6.3 Otimizar autoloader do Composer
composer dump-autoload --optimize --no-dev
```

### 7. Verificar Permissões de Arquivos

```bash
# Definir permissões corretas para storage e cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Se usar Apache/Nginx com usuário diferente, ajustar conforme necessário
```

### 8. Desativar Modo de Manutenção

```bash
# Reativar aplicação
php artisan up
```

### 9. Verificações Pós-Deploy

```bash
# 9.1 Verificar logs de erro
tail -n 50 storage/logs/laravel.log

# 9.2 Testar rotas principais (via curl ou navegador)
curl -I https://maieuticavaliacom.br
curl -I https://maieuticavaliacom.br/login

# 9.3 Verificar filas (se usar)
php artisan queue:work --stop-when-empty

# 9.4 Verificar status geral
php artisan about
```

---

## 🆘 Rollback em Caso de Problema

Se algo der errado durante a atualização:

### Opção 1: Reverter Código

```bash
# 1. Ativar modo de manutenção novamente
php artisan down

# 2. Reverter para commit anterior
git log --oneline -5  # Ver últimos commits
git reset --hard HASH_DO_COMMIT_ANTERIOR

# 3. Reinstalar dependências da versão anterior
composer install --no-dev
npm ci --production
npm run production

# 4. Limpar caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 5. Reativar aplicação
php artisan up
```

### Opção 2: Restaurar Banco de Dados

```bash
# ⚠️ USE COM CUIDADO - Isso SOBRESCREVE o banco atual!

# 1. Ativar modo de manutenção
php artisan down

# 2. Restaurar backup
mysql -u usuario_db -p nome_banco < backup_YYYYMMDD_HHMMSS.sql

# 3. Limpar cache
php artisan cache:clear

# 4. Reativar aplicação
php artisan up
```

---

## 📝 Casos Específicos

### Atualizar Apenas Permissões (Seeders)

```bash
# Sem modo de manutenção (se for rápido)
php artisan db:seed --class=RoleAndPermissionSeeder --force
php artisan cache:clear
```

### Atualizar Apenas Frontend (Assets)

```bash
# 1. Modo de manutenção
php artisan down

# 2. Atualizar código
git pull origin main

# 3. Recompilar assets
npm ci --production
npm run production

# 4. Limpar cache de views
php artisan view:clear

# 5. Reativar
php artisan up
```

### Atualizar Apenas Backend (PHP)

```bash
# 1. Modo de manutenção
php artisan down

# 2. Atualizar código
git pull origin main

# 3. Atualizar dependências
composer install --no-dev --optimize-autoloader

# 4. Executar migrações (se houver)
php artisan migrate --force

# 5. Limpar e recriar caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# 6. Reativar
php artisan up
```

### Adicionar Novas Permissões

```bash
# 1. Atualizar código (sem modo de manutenção se for rápido)
git pull origin main

# 2. Executar seeder de permissões
php artisan db:seed --class=RoleAndPermissionSeeder --force

# 3. Limpar cache de permissões
php artisan cache:clear
php artisan config:clear

# 4. Verificar permissões criadas (opcional)
php artisan tinker
>>> \Spatie\Permission\Models\Permission::where('name', 'like', 'document-%')->get();
>>> exit
```

---

## 🔍 Verificação de Saúde do Sistema

Após qualquer atualização, execute estas verificações:

```bash
# 1. Status geral da aplicação
php artisan about

# 2. Verificar conexão com banco de dados
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit

# 3. Verificar logs recentes
tail -n 100 storage/logs/laravel.log | grep -i error

# 4. Verificar permissões de arquivos
ls -la storage/logs/
ls -la bootstrap/cache/

# 5. Testar login e funcionalidades críticas manualmente
# - Login de usuário
# - Criar/editar registro
# - Gerar PDF
# - Upload de imagem
```

---

## 📊 Monitoramento Pós-Deploy

Após deploy bem-sucedido, monitore por pelo menos 30 minutos:

1. **Logs em tempo real:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Erros HTTP no servidor web:**
   ```bash
   # Apache
   tail -f /var/log/apache2/error.log

   # Nginx
   tail -f /var/log/nginx/error.log
   ```

3. **Performance:**
   - Tempo de resposta das páginas
   - Uso de memória/CPU
   - Erros 500/404

4. **Funcionalidades críticas:**
   - Login/logout
   - CRUD de registros principais
   - Geração de relatórios/PDFs
   - Upload de arquivos

---

## ⚙️ Configurações de Ambiente (.env)

Se precisar atualizar variáveis de ambiente:

```bash
# 1. Editar .env
nano .env

# 2. SEMPRE limpar cache de config após alterar .env
php artisan config:clear

# 3. Recriar cache (opcional, mas recomendado em produção)
php artisan config:cache
```

⚠️ **Variáveis que afetam cache:**
- `APP_ENV=production`
- `APP_DEBUG=false` (NUNCA true em produção!)
- `CACHE_DRIVER`
- `SESSION_DRIVER`
- `QUEUE_CONNECTION`

---

## 🛡️ Boas Práticas de Segurança

### Antes de Fazer Deploy

- [ ] `APP_DEBUG=false` no `.env` de produção
- [ ] `APP_ENV=production` no `.env` de produção
- [ ] Senhas fortes em `DB_PASSWORD`, `MAIL_PASSWORD`, etc.
- [ ] `RECAPTCHA_SITE_KEY` e `RECAPTCHA_SECRET_KEY` configurados
- [ ] Certificado SSL ativo (HTTPS)

### Durante o Deploy

- [ ] Usar `composer install --no-dev` (não instalar dependências de desenvolvimento)
- [ ] Usar `npm ci --production` (dependências de produção apenas)
- [ ] Nunca commitar `.env` no Git
- [ ] Nunca expor backups em diretórios públicos

### Após o Deploy

- [ ] Verificar que debug está desativado (sem stack traces visíveis)
- [ ] Testar ReCAPTCHA em formulários de login
- [ ] Verificar HTTPS em todas as páginas
- [ ] Testar permissões de acesso (usuários não devem acessar áreas restritas)

---

## 📞 Contatos e Suporte

Em caso de problemas críticos:

1. **Verificar logs:** `storage/logs/laravel.log`
2. **Ativar modo de manutenção:** `php artisan down`
3. **Fazer rollback se necessário**
4. **Documentar o erro** com:
   - Mensagem de erro completa
   - Passos que causaram o problema
   - Commit/branch que gerou o problema
   - Hora exata do incidente

---

## 📚 Referências Rápidas

### Comandos Úteis do Laravel

```bash
# Informações do sistema
php artisan about

# Limpar tudo
php artisan optimize:clear

# Otimizar tudo para produção
php artisan optimize

# Ver rotas
php artisan route:list

# Ver configurações
php artisan config:show

# Testar conexão com banco
php artisan db:show

# Ver migrações pendentes
php artisan migrate:status
```

### Estrutura de Diretórios Importantes

```
/var/www/maieutica/
├── .env                    # Configurações de ambiente (NUNCA versionar!)
├── storage/
│   ├── logs/              # Logs da aplicação
│   └── app/public/        # Uploads de usuários
├── public/
│   ├── images/            # Imagens estáticas
│   └── build/             # Assets compilados
└── bootstrap/cache/       # Cache de bootstrap
```

---

## ✅ Checklist Final Pós-Deploy

- [ ] Aplicação está fora do modo de manutenção
- [ ] Não há erros no `laravel.log`
- [ ] Login funciona corretamente
- [ ] Funcionalidades críticas testadas
- [ ] Permissões de arquivos corretas (775 storage/)
- [ ] Cache otimizado (config, route, view)
- [ ] Backup salvo em local seguro
- [ ] Deploy documentado (data, hora, mudanças)
- [ ] Monitoramento ativo por 30+ minutos

---

**Última atualização:** 2025-12-21
**Versão do documento:** 1.0
**Sistema:** Maiêutica - Plataforma de Avaliação Clínica
