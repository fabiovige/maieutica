## Maiêutica - clínica psicológica e terapias associadas

### Sistema de avaliação cognitiva

Colocar no etc/hosts de sua máquina:
```
127.0.0.1 project.test
```

Copiar o .env.example e alterar as variáveis de ambiente:
```
cp .env.example .env
```

Criar as imagens e containers do docker:
```
docker-compose up -d --build
```

Entrar no container para rodar composer e etc do projeto:
```
docker-compose exec app bash
```

Rodar o composer, dentro do container:
```
rm -rf vendor && composer install
```

Gerar a chave do laravel, dentro do container:
```
php artisan key:generate
```

Rodar as migrations e os seeders, dentro do container:
```
php artisan migrate --seed
```

```
Ajustar as permissões, dentro do container:
```
chmod -R 777 storage && chmod -R 777 bootstrap/cache
```

Instalar o npm, dentro do container:
```
rm -rf node_modules && npm install && npm run dev
```

Acessar a aplicação via browser:
http://project.test
