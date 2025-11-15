# 🚀 Deploy do Sistema de Documentos em Produção

**ATENÇÃO:** Este sistema está em produção. Siga os passos com cuidado para manter a integridade do banco de dados.

---

## ⚠️ PRÉ-REQUISITOS

- [x] Branch `feature/document-templates` mesclada na `develop` ou `main`
- [x] Código atualizado no servidor de produção
- [x] Acesso SSH ao servidor
- [x] Backup do banco de dados

---

## 📋 PASSO A PASSO

### **1. BACKUP DO BANCO DE DADOS** ⚠️ OBRIGATÓRIO

```bash
# Fazer backup do banco ANTES de qualquer alteração
php artisan db:backup

# OU manualmente via mysqldump
mysqldump -u [usuario] -p [nome_banco] > backup_pre_documentos_$(date +%Y%m%d_%H%M%S).sql
```

**IMPORTANTE:** Guarde este backup em local seguro!

---

### **2. ATUALIZAR CÓDIGO NO SERVIDOR**

```bash
# Entrar no diretório da aplicação
cd /caminho/para/maieutica

# Atualizar código do repositório
git pull origin main  # ou develop, dependendo da branch

# Instalar dependências (se houver novas)
composer install --no-dev --optimize-autoloader
```

---

### **3. EXECUTAR MIGRATIONS** 📊

**As migrations são SEGURAS** - Elas apenas **criam novas tabelas**, não alteram tabelas existentes.

```bash
# Listar migrations pendentes (verificação)
php artisan migrate:status

# Executar APENAS as migrations novas
php artisan migrate --path=database/migrations/2025_11_15_110332_create_document_templates_table.php
php artisan migrate --path=database/migrations/2025_11_15_110611_create_generated_documents_table.php

# OU executar todas as pendentes de uma vez
php artisan migrate --force
```

**O que será criado:**
- ✅ Tabela `document_templates` (nova)
- ✅ Tabela `generated_documents` (nova)
- ✅ **NÃO** altera nenhuma tabela existente

**Verificar se deu certo:**
```bash
# Verificar se as tabelas foram criadas
php artisan tinker
>>> DB::table('document_templates')->count();
=> 0
>>> DB::table('generated_documents')->count();
=> 0
>>> exit
```

---

### **4. EXECUTAR SEEDER DE PERMISSIONS** 🔐

**O seeder é SEGURO** - Usa `firstOrCreate()`, então:
- ✅ Cria apenas permissions que não existem
- ✅ **NÃO** duplica permissions existentes
- ✅ **NÃO** remove permissions antigas
- ✅ Atualiza roles (admin, profissional, responsavel) com novas permissions

```bash
# Executar seeder de permissions
php artisan db:seed --class=RoleAndPermissionSeeder --force
```

**O que será adicionado:**
- ✅ 17 novas permissions:
  - `template-list`, `template-list-all`
  - `template-show`, `template-show-all`
  - `template-create`
  - `template-edit`, `template-edit-all`
  - `template-delete`, `template-delete-all`
  - `document-generate`
  - `document-list`, `document-list-all`
  - `document-show`, `document-show-all`
  - `document-download`
  - `document-delete`, `document-delete-all`

- ✅ Atualiza roles:
  - **Admin:** Recebe TODAS as 17 permissions
  - **Profissional:** Recebe `template-list`, `template-show`, `document-generate`, `document-list`, `document-show`, `document-download`
  - **Responsável:** Recebe `document-list`, `document-show`, `document-download`

**Verificar se deu certo:**
```bash
php artisan tinker
>>> \Spatie\Permission\Models\Permission::where('name', 'like', 'template-%')->count();
=> 9
>>> \Spatie\Permission\Models\Permission::where('name', 'like', 'document-%')->count();
=> 8
>>> exit
```

---

### **5. LIMPAR CACHES** 🧹

```bash
# Limpar todos os caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recriar cache otimizado
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### **6. VERIFICAR PERMISSÕES DE STORAGE** 📁

O sistema salva PDFs em `storage/app/documents/`. Garantir que o servidor web tem permissão de escrita:

```bash
# Criar diretório de documentos
mkdir -p storage/app/documents

# Dar permissões corretas (Linux/Mac)
chmod -R 775 storage
chown -R www-data:www-data storage  # ou seu usuário do servidor web

# No Windows (servidor local), não precisa
```

---

### **7. TESTAR O SISTEMA** ✅

**Teste 1: Acessar Templates**
```
URL: https://maieuticavalia.com.br/document-templates
Resultado esperado: Página de listagem (vazia inicialmente)
```

**Teste 2: Criar Template**
```
URL: https://maieuticavalia.com.br/document-templates/create
Ação: Preencher formulário e salvar
Resultado esperado: Template criado com sucesso
```

**Teste 3: Gerar Documento**
```
URL: https://maieuticavalia.com.br/generated-documents/create
Ação: Selecionar criança + template e gerar
Resultado esperado: PDF gerado e disponível para download
```

**Teste 4: Verificar Menu**
```
Resultado esperado: Menu "Documentos" visível no topo
Submenus: Templates, Documentos Gerados, Gerar Documento
```

---

## 🔙 ROLLBACK (Se algo der errado)

### **Reverter Migrations:**

```bash
# Reverter as 2 migrations de documentos
php artisan migrate:rollback --step=2

# OU especificar o batch
php artisan migrate:rollback --batch=[numero_do_batch]
```

### **Remover Permissions (Se necessário):**

```bash
php artisan tinker
>>> \Spatie\Permission\Models\Permission::where('name', 'like', 'template-%')->delete();
>>> \Spatie\Permission\Models\Permission::where('name', 'like', 'document-%')->delete();
>>> exit
```

### **Restaurar Backup:**

```bash
# Restaurar backup do banco
mysql -u [usuario] -p [nome_banco] < backup_pre_documentos_YYYYMMDD_HHMMSS.sql
```

---

## 📊 RESUMO DOS ARQUIVOS MODIFICADOS

### **Novos Arquivos (Não afetam código existente):**
```
database/migrations/
├── 2025_11_15_110332_create_document_templates_table.php
└── 2025_11_15_110611_create_generated_documents_table.php

app/Models/
├── DocumentTemplate.php
└── GeneratedDocument.php

app/Policies/
├── DocumentTemplatePolicy.php
└── GeneratedDocumentPolicy.php

app/Services/
└── DocumentGeneratorService.php

app/Http/Controllers/
├── DocumentTemplateController.php
└── GeneratedDocumentController.php

resources/views/document-templates/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
├── show.blade.php
└── trash.blade.php

resources/views/generated-documents/
├── index.blade.php
├── create.blade.php
└── show.blade.php
```

### **Arquivos Modificados (Integrações):**
```
routes/web.php                              # + 14 rotas novas
app/Providers/AuthServiceProvider.php      # + 2 policies registradas
database/seeders/RoleAndPermissionSeeder.php  # + 17 permissions
resources/views/layouts/menu.blade.php      # + Menu "Documentos"
resources/views/kids/overview.blade.php     # + Botão "Gerar Documento"
```

---

## ✅ CHECKLIST PÓS-DEPLOY

- [ ] Backup do banco realizado
- [ ] Código atualizado no servidor
- [ ] Migrations executadas com sucesso
- [ ] Seeder de permissions executado
- [ ] Caches limpos
- [ ] Permissões de storage configuradas
- [ ] Teste de acesso a `/document-templates` OK
- [ ] Teste de criação de template OK
- [ ] Teste de geração de documento OK
- [ ] Menu "Documentos" visível OK
- [ ] Botão "Gerar Documento" em Kids/Overview visível OK

---

## 📞 SUPORTE

Em caso de problemas:
1. Verificar logs: `storage/logs/laravel.log`
2. Verificar permissions do storage: `ls -la storage/`
3. Verificar se migrations rodaram: `php artisan migrate:status`
4. Verificar se permissions foram criadas: `php artisan tinker` → `Permission::count()`

---

## 🎯 COMANDOS RÁPIDOS (Copiar e Colar)

```bash
# DEPLOY COMPLETO
cd /caminho/para/maieutica
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=RoleAndPermissionSeeder --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage
chown -R www-data:www-data storage
```

---

**Data do Deploy:** ___/___/______
**Responsável:** _________________
**Status:** [ ] Sucesso  [ ] Rollback necessário

---

**FIM DO DOCUMENTO**
