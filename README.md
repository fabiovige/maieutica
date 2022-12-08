## Maiêutica - clínica psicológica e terapias associadas

### Sistema de avaliação cognitiva

#### INSTALAÇÃO

Colocar no etc/hosts de sua máquina:
```
127.0.0.1 maieutica.test
```

Copiar o .env.example e alterar as variáveis de ambiente:
```
cp .env.example .env
```

Habilitando permissões
```
sudo chown -R $USER:$USER maieutica.test
sudo chmod 777 -R storage && chmod 777 bootstrap && chmod 777 resources
```

Abilitando reescrita de url no apache2
```
sudo a2enmod rewrite
```

Em seguida, edite o arquivo /etc/apache2/apache2.conf e procure pelo seguinte trecho:
```
<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

VHOSTS
######################################

Clonando o projeto
```
cd /var/www
git clone git@github.com:fabiovige/maieutica.git maieutica.test
```

Adicionando mais as permissões
```
sudo chown -R $USER:$USER /var/www/maieutica.test
sudo chmod -R 755 /var/www
```

Criando virtualhost
```
sudo nano /etc/apache2/sites-available/maieutica.test.conf

<VirtualHost *:80>
    ServerName maieutica.test
    DocumentRoot /var/www/maieutica.test/public
</VirtualHost>
```

Habilitando virtualhost
```
sudo a2ensite maieutica.test.conf
sudo systemctl restart apache2
```

```
Desabilitando virtualhost
sudo a2dissite maieutica.test.conf
sudo systemctl restart apache2
```

Filas
```
php artisan queue:work --queue=emails
```

Acesse:
http://maieutica.test


