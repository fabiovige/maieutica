# Plano: Implementar PWA (Progressive Web App) - "Instalar como App"

## Contexto

90% dos usuarios acessam o Maieutica pelo celular (Android/Chrome). O recurso de "Adicionar a tela inicial" (Add to Home Screen) permite que o usuario instale o site como se fosse um app nativo — abre em tela cheia, sem barra do navegador, com icone proprio na home screen. Isso se chama **PWA (Progressive Web App)**.

**Estado atual:** O projeto NAO tem nenhum recurso PWA implementado — nao existe manifest.json, service worker, meta tags PWA, nem icones nos tamanhos necessarios. O favicon.ico existe mas esta VAZIO (0 bytes).

**Dificuldade:** Baixa a media. A implementacao minima eh simples e nao requer pacotes adicionais.

---

## Requisitos do Chrome para o prompt "Instalar App"

O Chrome exige TODOS estes itens para mostrar o botao de instalacao:

1. **HTTPS** — ja funciona em producao (maieuticavaliacom.br)
2. **Web App Manifest** (`manifest.json`) com: `name`, `icons` (192x192 + 512x512), `start_url`, `display: "standalone"`
3. **Service Worker registrado** com um handler de `fetch` — NAO precisa cachear nada, so precisa existir
4. **Interacao do usuario** — Chrome espera o usuario interagir com a pagina antes de mostrar o prompt (automatico)

---

## Etapas de Implementacao

### Etapa 1: Gerar Icones PWA (manual/design)

Criar icones quadrados a partir do logotipo existente. Colocar em `public/images/icons/`:

| Arquivo | Tamanho | Obrigatorio? |
|---------|---------|-------------|
| `icon-72x72.png` | 72x72 | Opcional |
| `icon-96x96.png` | 96x96 | Opcional |
| `icon-128x128.png` | 128x128 | Opcional |
| `icon-144x144.png` | 144x144 | Opcional (MS Tile) |
| `icon-152x152.png` | 152x152 | Opcional (Apple) |
| `icon-192x192.png` | 192x192 | **SIM** |
| `icon-384x384.png` | 384x384 | Opcional |
| `icon-512x512.png` | 512x512 | **SIM** |

- Usar fundo com a cor da marca `#AD6E9B` ou branco
- Tambem substituir `public/favicon.ico` (atualmente 0 bytes) por um favicon real

### Etapa 2: Criar `public/manifest.json`

Arquivo estatico (servido direto pelo Apache, sem passar pelo Laravel):

```json
{
    "name": "Maieutica - Avaliacao Cognitiva",
    "short_name": "Maieutica",
    "description": "Plataforma clinica de avaliacao cognitiva",
    "start_url": "/",
    "display": "standalone",
    "background_color": "#f8fafc",
    "theme_color": "#AD6E9B",
    "orientation": "any",
    "scope": "/",
    "lang": "pt-BR",
    "icons": [/* todos os tamanhos */]
}
```

### Etapa 3: Criar `public/sw.js` (Service Worker minimo)

```javascript
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', (event) => event.respondWith(fetch(event.request)));
```

- **SEM cache, SEM modo offline** — o sistema clinico precisa de dados em tempo real
- O handler `fetch` eh obrigatorio para o Chrome aceitar como PWA
- Apenas repassa todas as requisicoes para a rede normalmente

### Etapa 4: Criar partial Blade `resources/views/layouts/partials/pwa-meta.blade.php`

Centraliza todas as meta tags PWA + registro do service worker:

```blade
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#AD6E9B">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Maieutica">
<link rel="apple-touch-icon" href="/images/icons/icon-152x152.png">
<meta name="msapplication-TileImage" content="/images/icons/icon-144x144.png">
<meta name="msapplication-TileColor" content="#AD6E9B">
<link rel="icon" type="image/png" sizes="32x32" href="/images/icons/icon-96x96.png">

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js');
    });
}
</script>
```

### Etapa 5: Incluir o partial nos layouts

Adicionar `@include('layouts.partials.pwa-meta')` no `<head>` de:

1. `resources/views/layouts/app.blade.php` — layout principal (todas as paginas autenticadas)
2. `resources/views/auth/login.blade.php` — pagina de login (standalone)
3. `resources/views/auth/passwords/email.blade.php` — recuperar senha
4. `resources/views/auth/passwords/reset.blade.php` — redefinir senha

---

## Resumo de Arquivos

### Novos arquivos (6+):
| Arquivo | Tipo |
|---------|------|
| `public/manifest.json` | JSON estatico |
| `public/sw.js` | JavaScript estatico |
| `public/images/icons/*.png` | Icones (8 tamanhos) |
| `resources/views/layouts/partials/pwa-meta.blade.php` | Blade partial |

### Arquivos modificados (4):
| Arquivo | Mudanca |
|---------|---------|
| `resources/views/layouts/app.blade.php` | +1 linha `@include` |
| `resources/views/auth/login.blade.php` | +1 linha `@include` |
| `resources/views/auth/passwords/email.blade.php` | +1 linha `@include` |
| `resources/views/auth/passwords/reset.blade.php` | +1 linha `@include` |

### Arquivos NAO modificados:
- `webpack.mix.js` — sem mudancas no build
- `resources/js/app.js` — registro do SW vai no Blade, nao no JS compilado
- `package.json` — nenhum pacote npm novo
- `composer.json` — nenhum pacote PHP novo
- `routes/web.php` — manifest e SW sao arquivos estaticos
- `app/Http/Kernel.php` — sem middleware novo

---

## Riscos e Mitigacoes

| Risco | Mitigacao |
|-------|-----------|
| SW cacheando CSRF tokens (erro 419) | SW nao cacheia NADA — repassa tudo para a rede |
| PageSpeed middleware corrompendo meta tags | Nao afeta `<link>` ou `<meta>` — sem risco |
| SW interceptando download de PDFs | SW repassa fetch direto — sem risco |
| Icones nao aparecendo em dispositivos | Gerar com fundo solido e padding adequado |

---

## Verificacao

1. **Local (http://maieutica.test):**
   - Chrome DevTools > Application > Manifest — verificar se carrega sem erros
   - Chrome DevTools > Application > Service Workers — verificar registro
   - (Prompt de instalacao NAO aparece em HTTP — esperado)

2. **Producao (https://maieuticavaliacom.br):**
   - Android Chrome: navegar no site, o prompt "Adicionar a tela inicial" deve aparecer
   - Ou: menu 3 pontos > "Instalar app"
   - iOS Safari: "Compartilhar" > "Adicionar a Tela de Inicio"

3. **Lighthouse:** rodar auditoria PWA — deve passar em "Installable"

4. **Checklist:**
   - [ ] manifest.json acessivel em /manifest.json
   - [ ] Icones carregam corretamente
   - [ ] SW registrado e ativo
   - [ ] Login funciona normalmente (sem erro 419)
   - [ ] PDFs geram normalmente
   - [ ] Vue components funcionam
   - [ ] Instalacao funciona no Android
   - [ ] Icone aparece na home screen do iOS
