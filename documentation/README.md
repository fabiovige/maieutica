# 📚 Documentação Maiêutica - Sistema de Avaliação Cognitiva Infantil

## 🎯 Sobre esta Documentação

Esta é a documentação oficial do sistema **Maiêutica** para profissionais de clínicas de psicologia. O objetivo é fornecer um guia completo e intuitivo para que psicólogos clínicos aprendam a utilizar todas as funcionalidades do sistema.

## 📂 Estrutura do Projeto

```
documentation/
├── index.html                          # Página inicial (menu principal)
├── assets/
│   ├── css/
│   │   └── style.css                   # Estilos customizados
│   ├── js/
│   │   └── script.js                   # Interatividade (busca, navegação, lightbox)
│   └── images/
│       ├── screenshots/                # (Futuro) Capturas de tela do sistema
│       └── icons/                      # (Futuro) Ícones ilustrativos
├── pages/
│   ├── 01-introducao.html              # Conceitos fundamentais
│   ├── 02-primeiros-passos.html        # Login, dashboard, navegação
│   ├── 03-gestao-criancas.html         # Cadastrar e gerenciar pacientes
│   ├── 04-criar-avaliacao.html         # Iniciar novo checklist
│   ├── 05-preencher-avaliacao.html     # Atribuir notas (0-3)
│   ├── 06-visualizar-resultados.html   # Gráficos e percentuais
│   ├── 07-reavaliacao.html             # Comparar evolução
│   ├── 08-planos-desenvolvimento.html  # Criar planos de intervenção
│   ├── 09-gestao-profissionais.html    # Admin: gerenciar usuários
│   ├── 10-relatorios-pdf.html          # Exportar relatórios
│   ├── 11-biblioteca-competencias.html # Navegar biblioteca
│   ├── 12-dicas-boas-praticas.html     # Recomendações de uso
│   └── 13-faq.html                     # Perguntas frequentes
└── README.md                           # Este arquivo
```

## 🚀 Como Usar

### Acessando a Documentação

1. **Localmente (desenvolvimento):**
   - Abra `documentation/index.html` diretamente no navegador
   - Ou use um servidor local:
     ```bash
     # Python 3
     cd documentation
     python -m http.server 8000
     # Acesse: http://localhost:8000
     ```

2. **Em Produção:**
   - Hospede a pasta `documentation/` em servidor web (Apache, Nginx)
   - Ou integre ao sistema Laravel (copie para `public/docs/`)

### Navegação

- **Menu lateral:** Clique nas seções para navegar
- **Busca:** Use o campo de busca no topo do menu lateral
- **Atalhos de teclado:**
  - `←` / `→`: Navegar entre páginas
  - `Esc`: Fechar modais
  - Clique em imagens (quando houver) para ampliar (lightbox)

## 🎨 Características Técnicas

### Frontend
- **HTML5 Semântico:** Estrutura acessível e otimizada para SEO
- **CSS3 Puro:** Sem frameworks pesados, design responsivo com CSS Grid/Flexbox
- **JavaScript Vanilla:** Interatividade sem dependências externas
- **Ícones:** Bootstrap Icons (CDN)

### Funcionalidades JavaScript
- ✅ Busca em tempo real no menu lateral
- ✅ Marcação automática de página ativa
- ✅ Botão "voltar ao topo" com scroll suave
- ✅ Navegação por teclado (setas esquerda/direita)
- ✅ Barra de progresso de leitura
- ✅ Lightbox para screenshots (quando houver imagens)
- ✅ Animações de entrada (fade in) nos elementos
- ✅ Tooltips customizados
- ✅ Preparado para impressão (esconde sidebar/header)

### Design Responsivo
- **Desktop:** Experiência completa com sidebar fixa
- **Tablet:** Sidebar colapsável com botão toggle
- **Mobile:** Layout otimizado para leitura vertical

## 📝 Conteúdo das Páginas

| Página | Título | Descrição | Badge |
|--------|--------|-----------|-------|
| `index.html` | Menu Principal | Visão geral e cards de navegação | - |
| `01-introducao.html` | Introdução | O que é Maiêutica, conceitos fundamentais (Kid, Checklist, Competência, Domínio, Escala 0-3) | Básico |
| `02-primeiros-passos.html` | Primeiros Passos | Login, dashboard, menu principal, atalhos de teclado | Básico |
| `03-gestao-criancas.html` | Gestão de Crianças | Cadastrar, editar, visualizar ficha, overview de evolução | Essencial |
| `04-criar-avaliacao.html` | Criar Avaliação | Iniciar checklist, selecionar paciente, filtrar competências, clonar avaliação | Essencial |
| `05-preencher-avaliacao.html` | Preencher Avaliação | Como atribuir notas (0-3), observações clínicas, finalizar | Essencial |
| `06-visualizar-resultados.html` | Visualizar Resultados | Interpretar gráfico radar, percentuais por domínio, código de cores | Intermediário |
| `07-reavaliacao.html` | Reavaliação | Quando reavaliar, comparar evolução, gráfico de linha temporal | Intermediário |
| `08-planos-desenvolvimento.html` | Planos de Desenvolvimento | Criar planos, sugestão automática de competências, acompanhar progresso | Avançado |
| `09-gestao-profissionais.html` | Gestão de Profissionais | Adicionar usuários, perfis de acesso (Super Admin, Admin, Profissional, Visualizador) | Admin |
| `10-relatorios-pdf.html` | Relatórios PDF | Gerar PDF de checklist, overview, plano, compartilhar | Essencial |
| `11-biblioteca-competencias.html` | Biblioteca de Competências | Navegar, buscar, adicionar/editar competências (admin) | Intermediário |
| `12-dicas-boas-praticas.html` | Dicas e Boas Práticas | Periodicidade, observações efetivas, fluxo de trabalho, LGPD | Recomendado |
| `13-faq.html` | Perguntas Frequentes | Login, pacientes, avaliações, gráficos, planos, PDF, problemas técnicos, suporte | Suporte |

## 🎯 Público-Alvo

- **Primário:** Psicólogos clínicos especializados em atendimento infantil
- **Secundário:** Coordenadores de clínicas, supervisores
- **Terciário:** Administradores de sistema

## 🔧 Manutenção e Atualização

### Adicionando Novas Páginas

1. Crie arquivo HTML em `pages/XX-nome-pagina.html`
2. Copie estrutura de página existente (header, sidebar, main, footer)
3. Adicione link no menu lateral de **todas as páginas** (sidebar-nav)
4. Adicione link na `index.html` (card ou tabela)
5. Atualize navegação (prev/next) nas páginas adjacentes

### Adicionando Screenshots

1. Coloque imagens em `assets/images/screenshots/`
2. Substitua `.screenshot-placeholder` por:
   ```html
   <div class="screenshot">
       <img src="../assets/images/screenshots/nome-imagem.png" alt="Descrição">
       <div class="screenshot-caption">Legenda da imagem</div>
   </div>
   ```

### Personalizando Estilos

- Edite `assets/css/style.css`
- Variáveis CSS estão no início do arquivo (`:root`)
- Código de cores:
  - Primary: `#4a90e2` (azul)
  - Secondary: `#50c878` (verde)
  - Danger: `#e74c3c` (vermelho)
  - Warning: `#f39c12` (amarelo)

## 📊 Métricas de Uso (Futuro)

Para rastrear uso da documentação, considere integrar:
- Google Analytics
- Hotjar (mapa de calor)
- Feedback inline (botões "Útil" / "Não útil")

## 🔒 Segurança

Esta documentação é **somente leitura** e não acessa o banco de dados. Contém apenas:
- Instruções de uso
- Screenshots (sem dados reais de pacientes)
- Exemplos fictícios

**Não inclui:**
- Credenciais de acesso
- Dados sensíveis de pacientes
- Informações de configuração de servidor

## 📞 Suporte

Para dúvidas sobre a documentação ou sugestões de melhoria:
- **Email:** suporte@maieuticavaliacom.br
- **Issue Tracker:** [GitHub Issues](https://github.com/seu-usuario/maieutica/issues) (se aplicável)

## 📄 Licença

© 2025 Maiêutica. Todos os direitos reservados.
Esta documentação é de uso exclusivo para clientes licenciados do sistema Maiêutica.

---

**Versão:** 1.0
**Última atualização:** Janeiro 2025
**Criado por:** Equipe Maiêutica
