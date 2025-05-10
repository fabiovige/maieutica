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

-   **Backend:** Laravel 9.x (PHP 8+)
-   **Frontend:** Vue 3, Bootstrap 5, Chart.js, SweetAlert2
-   **Banco de Dados:** MySQL/MariaDB (padrão Laravel)
-   **Outros:** Geração de PDFs, integração com DataTables, autenticação social, controle de permissões, logs administrativos.

## Estrutura do Projeto

-   `app/` - Lógica de negócio (Controllers, Models, Services, etc)
-   `resources/views/` - Templates Blade (HTML)
-   `resources/js/` - Componentes e lógica Vue 3
-   `public/` - Assets públicos (CSS, JS, imagens)
-   `routes/` - Rotas do sistema
-   `database/` - Migrations, seeders, factories

## Instalação e Uso

1. Clone o repositório
2. Instale as dependências PHP e JS:
    ```
    composer install
    npm install
    ```
3. Configure o `.env` e gere a chave:
    ```
    cp .env.example .env
    php artisan key:generate
    ```
4. Execute as migrations e seeders:
    ```
    php artisan migrate --seed
    ```
5. Compile os assets:
    ```
    npm run dev
    ```
6. Inicie o servidor:
    ```
    php artisan serve
    ```

## Licença

MIT
