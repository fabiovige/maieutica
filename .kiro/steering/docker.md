# Docker

Este projeto roda em Docker. **Todos os comandos** devem ser executados dentro do container.

## Regras

- NUNCA execute `php`, `composer`, `npm`, `artisan` ou qualquer comando diretamente no host.
- SEMPRE use `docker exec` para executar comandos dentro do container.
- SEMPRE rode comandos como o usuário `www-data` (`-u www-data`). O container abre como `root`, e qualquer artisan/composer rodado como root cria arquivos (logs, cache, sessions) com dono `root`, quebrando a aplicação servida pelo PHP-FPM (que roda como `www-data`, uid 1000). Ver "Permissões" abaixo.

## Container principal

O container principal chama-se **`maieutica_app`** (underscore, não hífen). PHP-FPM roda como `www-data`.

```bash
# Padrão para qualquer comando (sempre como www-data):
docker exec -u www-data maieutica_app <comando>

# Exemplos:
docker exec -u www-data maieutica_app php artisan migrate
docker exec -u www-data maieutica_app php artisan db:seed
docker exec -u www-data maieutica_app composer fresh
docker exec -u www-data maieutica_app npm run dev
docker exec -u www-data maieutica_app php artisan test
docker exec -u www-data maieutica_app ./vendor/bin/pint
```

> Use `-it` apenas para comandos interativos (ex.: `php artisan tinker`). Para comandos não interativos, `-it` é desnecessário.

## Verificar containers ativos

```bash
docker ps --filter "name=maieutica"
```

Containers do stack: `maieutica_app` (PHP-FPM), `maieutica_nginx`, `maieutica_db` (mariadb), `maieutica_queue`, `maieutica_scheduler`, `maieutica_node`, `maieutica_mailhog`.

Se o container não estiver rodando ou o nome for diferente, verifique com `docker ps` e ajuste.

## Permissões (storage / cache / logs)

Sintoma típico: `The stream or file ".../storage/logs/laravel-AAAA-MM-DD.log" could not be opened in append mode: Failed to open stream: Permission denied`.

**Causa:** algum processo rodou como `root` e criou o log do dia com dono `root`, impedindo o `www-data` (php-fpm) de gravar.

**Correção estrutural (já aplicada):** o `docker/php/entrypoint.sh` rebaixa todo comando PHP de aplicação para `www-data` via `gosu` — setup do `app`, `queue:work` e `schedule:run`. Só o *master* do `php-fpm` fica root (os workers já caem para `www-data`). O canal `daily` em `config/logging.php` cria o arquivo com `0664`. Assim o Laravel sempre cria o log com o dono correto. Mudanças no entrypoint/Dockerfile exigem `docker compose build app && docker compose up -d --force-recreate app queue scheduler`.

**Atenção — `docker exec` ignora o entrypoint.** Um `docker exec maieutica_app php artisan ...` sem `-u www-data` roda como **root** e recria o problema. Por isso a regra `-u www-data` continua obrigatória para comandos manuais (ver Regras).

**Remediação pontual** (se um arquivo já ficou com dono root):

```bash
docker exec maieutica_app sh -c 'chown -R www-data:www-data \
  /var/www/html/storage/logs \
  /var/www/html/storage/framework \
  /var/www/html/bootstrap/cache'
```

## Acesso web local

O domínio `maieutica.test` resolve para `127.0.0.1` via `/etc/hosts`.
Para acessar via browser/Playwright, use `http://maieutica.test` (porta 80, servido pelo nginx).
