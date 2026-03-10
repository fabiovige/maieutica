# Novo Layout com Sidebar Vertical

## Resumo das Alterações

Data: 2026-02-08

---

## 🎯 Objetivo

Transformar o layout de menu horizontal para sidebar vertical à esquerda, seguindo padrões de sistemas médicos/clínicos, com design responsivo para tablets e celulares.

---

## 📁 Arquivos Criados/Modificados

### Novos Arquivos

1. **`resources/views/layouts/sidebar.blade.php`**
   - Menu lateral vertical com navegação completa
   - Suporte a submenus colapsáveis
   - Seções organizadas: Denver, Prontuários, Documentos, Cadastros, Admin

2. **`resources/views/layouts/header.blade.php`**
   - Cabeçalho com breadcrumb à esquerda
   - Perfil do usuário à direita
   - Botão toggle para sidebar

3. **`resources/sass/_sidebar-layout.scss`**
   - Todos os estilos do novo layout
   - Responsividade completa
   - Animações e transições

### Arquivos Modificados

1. **`resources/views/layouts/app.blade.php`**
   - Estrutura completa reformulada
   - Container fluid em vez de container fixo
   - Scripts para toggle da sidebar

2. **`resources/sass/app.scss`**
   - Import do novo CSS de sidebar

3. **`CLAUDE.md`**
   - Documentação do novo sistema de layout

---

## 🏗️ Estrutura do Layout

```
┌─────────────────────────────────────────────────────────────┐
│  [LOGO]          Breadcrumb > Item          [Perfil] [Sair] │  ← Header (64px)
├──────────┬──────────────────────────────────────────────────┤
│          │                                                  │
│  MENU    │                                                  │
│  LATERAL │         CONTEÚDO FLUIDO                          │
│  (280px) │         (container-fluid)                        │
│          │                                                  │
│  • Item  │                                                  │
│  • Item  │                                                  │
│  • Item  │                                                  │
│          │                                                  │
└──────────┴──────────────────────────────────────────────────┘
         Footer (sticky bottom)
```

---

## 📱 Responsividade

| Breakpoint | Sidebar | Comportamento |
|------------|---------|---------------|
| ≥992px (Desktop) | Fixo, 280px | Pode colapsar para 70px |
| 768-991px (Tablet) | Drawer | Escondido, botão hamburger |
| <768px (Mobile) | Drawer full | Overlay escuro |

### Funcionalidades Mobile
- Botão hamburger no header abre sidebar
- Overlay escuro ao abrir sidebar
- Sidebar desliza da esquerda
- Fechar pelo botão X ou clicando no overlay

---

## 🎨 Design System

### Cores
- **Sidebar**: `#1e293b` (slate-800)
- **Texto sidebar**: `#94a3b8` (slate-400)
- **Texto ativo**: `#ffffff`
- **Header**: `#ffffff`
- **Fundo conteúdo**: `#f1f5f9`

### Tipografia
- Fonte: Nunito (já usada no sistema)
- Tamanho base: 14px
- Pesos: 400, 500, 600, 700

---

## 🔧 Como Usar

### Básico
```blade
@extends('layouts.app')

@section('title', 'Título da Página')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="#">Pai</a></li>
    <li class="breadcrumb-item active">Atual</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Conteúdo --}}
        </div>
    </div>
@endsection
```

### Com Ações no Header
```blade
@section('header-actions')
    <a href="#" class="btn btn-primary btn-sm">
        <i class="bi bi-plus"></i> Novo
    </a>
@endsection
```

---

## ♻️ Compatibilidade

O layout mantém compatibilidade com views antigas:

- `@section('actions')` → Funciona (mapeado para header-actions)
- `@section('breadcrumb')` → Funciona (substitui breadcrumb padrão)
- `@yield('title')` → Funciona (exibido no header)

---

## 🚀 Funcionalidades Extras

### 1. Sidebar Colapsável (Desktop)
- Clique no botão hamburger para colapsar
- Apenas ícones visíveis quando colapsado
- Estado salvo no localStorage

### 2. Submenus
- Lixeira possui submenu colapsável
- Indicador visual de expansão

### 3. Perfil do Usuário
- Dropdown com avatar
- Nome e email
- Links rápidos: Perfil, Dashboard
- Botão de logout

### 4. Badges na Lixeira
- Contadores de itens excluídos
- Atualizados automaticamente

---

## ⚠️ Notas Importantes

1. **Container Fluid**: O conteúdo agora usa `container-fluid` em vez de `container`, ocupando toda a largura disponível.

2. **Sidebar Fixo**: A sidebar é fixa (position: fixed), então o conteúdo principal tem margin-left igual à largura da sidebar.

3. **Mobile**: Em dispositivos móveis, a sidebar fica escondida por padrão e aparece como um drawer.

4. **Estado Persistente**: O estado da sidebar (colapsada/expandida) é salvo no localStorage do navegador.

---

## 🧪 Testar

1. Abra o sistema em um navegador desktop
2. Verifique se a sidebar aparece à esquerda
3. Clique no botão hamburger para colapsar/expandir
4. Redimensione para < 992px para testar responsividade
5. Em mobile, teste abrir/fechar a sidebar

---

## 📝 Próximos Passos (Opcional)

- [ ] Adicionar tema escuro/claro toggle
- [ ] Configurar atalhos de teclado
- [ ] Adicionar pesquisa global no header
- [ ] Implementar favoritos no menu
