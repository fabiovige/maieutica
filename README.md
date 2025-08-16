# Maiêutica - Plataforma de Avaliação Cognitiva para Clínicas Psicológicas

## Descrição

Maiêutica é um sistema web completo para clínicas psicológicas e terapias associadas, com foco em avaliação cognitiva de crianças, acompanhamento de progresso, gestão de profissionais, responsáveis e geração de relatórios detalhados.

## Funcionalidades Principais

-   **Gestão de Crianças/Pacientes:** Cadastro, acompanhamento, progresso e avaliações individuais.
-   **Checklists de Avaliação Cognitiva:** Criação, preenchimento, acompanhamento e análise de avaliações.
-   **Gestão de Competências:** Avaliação de habilidades/domínios cognitivos.
-   **Gestão de Profissionais:** Cadastro, associação e acompanhamento da equipe multidisciplinar.
-   **Gestão de Responsáveis:** Cadastro e vínculo de responsáveis legais.
-   **Gestão de Usuários, Papéis e Permissões:** Controle de acesso avançado para diferentes perfis.
-   **Dashboards e Relatórios:** Visualização de progresso, gráficos interativos e geração de PDFs.
-   **Interface Moderna e Responsiva:** Desenvolvida com Vue 3, Bootstrap 5, gráficos dinâmicos e alertas modernos.
-   **Segurança e Performance:** Autenticação robusta, permissões, proteção contra bots (reCAPTCHA) e otimizações automáticas.

## Tecnologias Utilizadas

-   **Backend:** Laravel 9.x (PHP 8.1)
-   **Frontend:** Vue 3, Bootstrap 5, Chart.js, SweetAlert2
-   **Banco de Dados:** MySQL 8.0
-   **Infraestrutura:** Docker + Docker Compose
-   **Servidor Web:** Nginx
-   **Outros:** Geração de PDFs, integração com DataTables, autenticação social, controle de permissões, logs administrativos.

## Estrutura do Projeto

-   `app/` - Lógica de negócio (Controllers, Models, Services, etc)
-   `resources/views/` - Templates Blade (HTML)
-   `resources/js/` - Componentes e lógica Vue 3
-   `public/` - Assets públicos (CSS, JS, imagens)
-   `routes/` - Rotas do sistema
-   `database/` - Migrations, seeders, factories
-   `docker/` - Configurações Docker (Nginx, etc)
-   `docker-compose.yml` - Orquestração dos containers

## Pré-requisitos

-   Docker
-   Docker Compose

## Instalação com Docker

### 1. Clone o repositório
```bash
git clone [repository-url]
cd maieutica
```

### 2. Inicie os containers
```bash
docker compose up -d
```

### 3. Instale as dependências PHP
```bash
docker compose exec app composer install
```

### 4. Configure o ambiente e gere a chave
```bash
# A aplicação já possui um .env configurado para Docker
docker compose exec app php artisan key:generate
```

### 5. Execute as migrations e seeders
```bash
docker compose exec app php artisan migrate:fresh --seed
```

### 6. Configure permissões do storage
```bash
docker compose exec app chown -R www-data:www-data storage
docker compose exec app chmod -R 775 storage
```

### 7. Acesse a aplicação
A aplicação estará disponível em: **http://localhost:3005**

## Comandos Úteis

### Desenvolvimento
```bash
# Instalar dependências Node.js
docker compose exec app npm ci

# Compilar assets para desenvolvimento
docker compose exec app npm run dev

# Compilar assets para produção
docker compose exec app npm run production

# Monitorar mudanças nos assets
docker compose exec app npm run watch
```

### Laravel Artisan
```bash
# Limpar caches
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Executar migrations
docker compose exec app php artisan migrate

# Executar seeders
docker compose exec app php artisan db:seed

# Acessar tinker
docker compose exec app php artisan tinker
```

### Logs e Monitoramento
```bash
# Ver logs dos containers
docker compose logs app
docker compose logs nginx
docker compose logs mysql

# Acessar log-viewer da aplicação
# http://localhost:3005/log-viewer
```

### Gerenciamento de Containers
```bash
# Parar containers
docker compose down

# Reiniciar containers
docker compose restart

# Rebuild containers
docker compose up -d --build

# Ver status dos containers
docker compose ps
```

## Informações dos Containers

-   **App (Laravel)**: PHP 8.1 FPM com todas as extensões necessárias
-   **Nginx**: Servidor web na porta 3005
-   **MySQL**: Banco de dados na porta 3306
    -   Database: `maieutica`
    -   User: `maieutica`
    -   Password: `secret`
    -   Root password: `root`

## Volumes Persistentes

-   **mysql_data**: Dados do MySQL persistem mesmo com containers reiniciados
-   **Código fonte**: Sincronizado em tempo real com o container

## Licença

MIT
