---
inclusion: manual
---

# Relatório de Desenvolvimento da Criança

## Visão Geral

O relatório de desenvolvimento é a principal ferramenta de análise cognitiva do sistema. Ele consolida os dados de um checklist Denver em métricas, gráficos e tabelas que mostram o progresso da criança por domínio de desenvolvimento. É acessível apenas para crianças Denver-elegíveis (até 60 meses) que possuam ao menos um checklist avaliado.

---

## Entrypoints

| Tela | Rota | Descrição |
|------|------|-----------|
| Desenvolvimento | `GET /kids/{kidId}/overview` | Visão geral com todos os níveis |
| Desenvolvimento por nível | `GET /kids/{kidId}/level/{levelId}/overview` | Filtrado por nível específico |
| Comparativo | `GET /analysis/{kidId}/level/{levelId}` | Comparação entre dois checklists |
| Comparativo com checklists | `GET /analysis/{kidId}/level/{levelId}/{first}/{second}` | Dois checklists selecionados |
| Detalhes de domínio | `GET /kids/{kidId}/level/{levelId}/domain/{domainId}` | Drill-down por domínio |
| Gerar PDF | `POST /kids/{kidId}/overview/pdf` | Download do relatório em PDF |

Todos os entrypoints aceitam `?checklist_id=X` como query string para selecionar um checklist específico. Sem esse parâmetro, usa o checklist mais recente da criança.

---

## `OverviewService::getOverviewData()`

É o núcleo de todo o relatório. Recebe `$kidId`, `$levelId` (opcional) e `$checklistId` (opcional) e retorna um array com todos os dados necessários para a view e o PDF.

### Dados retornados

```php
[
    'kid'                    => Kid,           // model da criança
    'ageInMonths'            => int,           // idade atual em meses
    'currentChecklist'       => Checklist|null,// checklist sendo analisado
    'allChecklists'          => Collection,    // todos os checklists da criança (para o select)
    'levelId'                => int|null,      // nível filtrado (null = todos)
    'levels'                 => array,         // [1, 2, 3, 4] até o nível máximo do checklist
    'domains'                => Collection,    // domínios filtrados pelo nível (ou todos)
    'domainData'             => array,         // dados calculados por domínio (ver abaixo)
    'totalItemsTested'       => int,           // total de competências avaliadas (note != 0)
    'totalItemsValid'        => int,           // total com note == 3 (Consistente)
    'totalItemsInvalid'      => int,           // testados - válidos
    'totalItemsTotal'        => int,           // total de competências no checklist
    'totalPercentage'        => float,         // percentual geral (0–100)
    'developmentalAgeInMonths' => float,       // ageInMonths * (totalPercentage / 100)
    'delayInMonths'          => float,         // ageInMonths - developmentalAgeInMonths
    'weakAreas'              => array,         // domínios com percentage <= 50% e itemsTested > 0
    'checklistId'            => int|null,      // ID do checklist selecionado
]
```

### Estrutura de `domainData` (por domínio)

```php
[
    'code'         => int,    // domain->id
    'name'         => string, // nome do domínio
    'initial'      => string, // sigla (ex: 'M', 'L', 'S')
    'abbreviation' => string, // igual a initial (alias para gráficos)
    'itemsTotal'   => int,    // total de competências do domínio
    'itemsTested'  => int,    // competências com note != 0
    'itemsValid'   => int,    // competências com note == 3
    'itemsInvalid' => int,    // itemsTested - itemsValid
    'percentage'   => float,  // (média das notas / 3) * 100
]
```

### Cálculo do `percentage` por domínio

O percentual por domínio **não é** simplesmente `itemsValid / itemsTested`. É calculado pela **média das notas** (escala 0–3) convertida para percentual:

```
sumNotes = soma de todas as notas != 0
countNotes = quantidade de competências com note != 0
average = sumNotes / countNotes          // escala 0–3
percentage = (average / 3) * 100        // escala 0–100
```

Isso significa que uma nota 2 (Em desenvolvimento) contribui parcialmente para o percentual, não apenas a nota 3 (Consistente).

### Cálculo do `totalPercentage` (geral)

```
totalPercentage = (totalItemsValid / totalItemsTested) * 100
```

O total geral usa apenas `itemsValid` (note == 3), diferente do cálculo por domínio que usa a média. Essa assimetria é intencional — o total geral é mais conservador.

### Áreas Frágeis (`weakAreas`)

Domínios com `percentage <= 50%` **e** `itemsTested > 0`. Ordenados por percentual crescente (pior primeiro). Domínios sem nenhuma avaliação são excluídos.

---

## Interface Web (View `kids/overview.blade.php`)

### Controles

- **Select de checklist** — troca o checklist analisado via `changeChecklist(id)` → redirect com `?checklist_id=X`.
- **Select de nível** — filtra por nível via `changeLevel(level)` → redirect para `/kids/{id}/level/{level}/overview`.

### 4 abas de visualização

| Aba | Conteúdo |
|-----|----------|
| Gráfico de Barras | Barras horizontais por domínio, percentual 0–100%, coloridas por `get_progress_color()` |
| Gráfico Radar | Radar por domínio em escala 0–3 (nota média), via componente `<x-radar-chart>` |
| Domínios e Áreas Frágeis | Tabela detalhada por domínio + tabela de áreas frágeis |
| Análise Geral | Gráfico de barras agrupado: Total / Avaliados / Válidos / Inválidos por domínio |

### Escala do gráfico radar

O radar usa escala **0–3** (nota média), não percentual. A conversão é:

```php
$radarDataScaled = ($domain['percentage'] / 100) * 3;
```

Isso é feito na view antes de passar ao componente `<x-radar-chart>`. O tooltip exibe o valor em percentual (`showPercentageInTooltip: true`).

---

## Tela Comparativa (`kids/radar_chart2.blade.php`)

Permite comparar dois checklists da mesma criança lado a lado.

- Selects de **Primeiro Checklist**, **Segundo Checklist** e **Nível** — qualquer mudança redireciona automaticamente.
- Gráfico de barras agrupado (percentual 0–100%) e gráfico radar (escala 0–3) com dois datasets.
- Lista lateral de domínios com link para drill-down (`kids.domainDetails`).
- Nível é obrigatório para a URL de comparação.

---

## PDF do Relatório de Desenvolvimento

**Rota:** `POST /kids/{kidId}/overview/pdf` (ou `/kids/{kidId}/level/{levelId}/overview/pdf`)

Gerado via **TCPDF** (`MyPdf`), não DomPDF. O fluxo é:

1. JavaScript captura os 3 canvas como base64 (`barChartImage`, `radarChartImage`, `barChartItems2Image`) via `canvas.toDataURL()`.
2. Envia via `fetch` POST com CSRF.
3. Controller reconstrói os dados via `OverviewService::getOverviewData()`.
4. Monta o PDF página a página.

### Estrutura do PDF (páginas)

| Página | Conteúdo |
|--------|----------|
| 1 | Logo + título "Relatório de Desenvolvimento" + foto da criança (se houver) + nome + idade em meses + profissionais + responsável + idade de desenvolvimento + atraso + período de avaliação |
| 2 | Gráfico de barras (percentual por domínio) |
| 3 | Gráfico radar (análise de competências) |
| 4 | Gráfico de análise geral (Total/Avaliados/Válidos/Inválidos) |
| 5 | Tabela de domínios avaliados com barra de progresso colorida |
| 6 | Tabela de áreas frágeis com barra de progresso colorida |

### Imagens dos gráficos no PDF

Os gráficos são capturados como PNG base64 no browser e enviados no POST. O controller:
1. Decodifica o base64.
2. Salva em arquivo temporário em `storage/app/temp/`.
3. Insere no PDF via `$pdf->Image()`.
4. Remove o arquivo temporário.

Se a imagem não for fornecida ou for inválida, exibe mensagem de erro no lugar do gráfico (não quebra o PDF).

### Nome do arquivo

```
relatorio-desenvolvimento_{slug-do-nome}_{YYYYMMDD-HHiiss}.pdf
```

### Cores das barras de progresso no PDF

O controller tem seu próprio método `getProgressColor()` com a paleta rosa da clínica (escala de `#6a2046` a `#f7e6f2`). **Diferente** do helper global `get_progress_color()` usado nas views web.

---

## Permissões

O acesso ao relatório de desenvolvimento usa a permissão `kid-show` (via `$this->authorize('view', $kid)`). Não há permissão específica para o relatório — quem pode ver a criança, pode ver o relatório.

---

## Regras de Negócio Críticas

1. **Sem checklist = sem relatório** — se `currentChecklist` for `null`, a view exibe estado vazio e o PDF retorna redirect com flash de aviso.
2. **`percentage` por domínio usa média das notas** — não é `itemsValid / itemsTested`. A fórmula é `(sumNotes / countNotes / 3) * 100`.
3. **`totalPercentage` usa apenas note == 3** — é mais conservador que o percentual por domínio.
4. **Radar usa escala 0–3** — converter `percentage / 100 * 3` antes de passar ao componente `<x-radar-chart>`.
5. **Áreas frágeis excluem domínios não avaliados** — `itemsTested > 0` é obrigatório.
6. **PDF usa TCPDF (`MyPdf`)** — não DomPDF. Não misturar as duas bibliotecas neste contexto.
7. **Gráficos no PDF vêm do browser** — o servidor não renderiza Chart.js. Se o JavaScript falhar em capturar o canvas, o PDF é gerado sem o gráfico (com mensagem de erro).
8. **Arquivos temporários de imagem** são criados em `storage/app/temp/` e removidos imediatamente após uso — nunca deixar acumular.
9. **`developmentalAgeInMonths`** é uma estimativa proporcional: `ageInMonths * (totalPercentage / 100)`. Não é um diagnóstico clínico.
10. **Filtro por nível** é opcional — sem `levelId`, todos os domínios e competências de todos os níveis do checklist são considerados.
