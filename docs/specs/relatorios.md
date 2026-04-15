# Plano de Relatorios - Maieutica

> Analise realizada em 10/03/2026 | Dados disponiveis no sistema para geracao de relatorios clinicos

---

## 1. Relatorios Recomendados

### Prioridade Alta (valor clinico imediato)

#### 1.1 Relatorio de Criancas Cadastradas
**Descricao:** Lista completa de criancas com dados demograficos e status de avaliacao.

| Campo | Fonte |
|-------|-------|
| Nome | `kids.name` |
| Data de nascimento | `kids.birth_date` |
| Idade (anos e meses) | Calculado |
| Genero | `kids.gender` |
| Etnia | `kids.ethnicity` |
| Responsavel | `responsibles.name` |
| Profissionais vinculados | `kid_professional` → `professionals` |
| Progresso geral (%) | `OverviewService` |
| Idade de desenvolvimento (meses) | `OverviewService` |
| Atraso (meses) | `OverviewService` |
| Qtd de checklists | Contagem |
| Data do ultimo checklist | `checklists.created_at` |

**Filtros:** genero, faixa etaria, profissional responsavel, faixa de progresso
**Exportar:** PDF, Excel

---

#### 1.2 Relatorio de Progresso Individual da Crianca
**Descricao:** Evolucao completa de uma crianca ao longo do tempo, com todos os checklists.

| Campo | Fonte |
|-------|-------|
| Dados da crianca | `kids.*` |
| Para cada checklist: data, nivel, % geral | `checklists` + `OverviewService` |
| % por dominio em cada checklist | `checklist_competence` agrupado por dominio |
| Areas fracas (<=50%) | `OverviewService.weakAreas` |
| Idade de desenvolvimento vs cronologica | Calculado |
| Evolucao entre checklists (delta %) | Comparacao entre checklists |
| Grafico de evolucao temporal | Chart.js → imagem base64 |

**Filtros:** periodo, nivel
**Exportar:** PDF (com graficos), Excel (dados tabulares)

---

#### 1.3 Relatorio de Desempenho por Dominio
**Descricao:** Analise detalhada de um dominio especifico para uma crianca ou grupo.

| Campo | Fonte |
|-------|-------|
| Dominio (nome, sigla, cor) | `domains.*` |
| Competencias do dominio | `competences.*` |
| Nota de cada competencia (0-3) | `checklist_competence.note` |
| Percentis de referencia (p25/50/75/90) | `competences.percentil_*` |
| % de dominio do aluno vs referencia | Calculado |
| Competencias dominadas (nota 3) | Filtro note=3 |
| Competencias emergentes (nota 1) | Filtro note=1 |
| Competencias nao testadas (nota 0) | Filtro note=0 |

**Filtros:** dominio, nivel, crianca
**Exportar:** PDF, Excel

---

#### 1.4 Relatorio de Prontuarios (Sessoes Clinicas)
**Descricao:** Historico de sessoes clinicas de uma crianca ou adulto.

| Campo | Fonte |
|-------|-------|
| Paciente (nome, tipo) | `medical_records.patient_type/id` |
| Data da sessao | `medical_records.session_date` |
| Queixa | `medical_records.complaint` |
| Objetivo/Tecnica | `medical_records.objective_technique` |
| Notas de evolucao | `medical_records.evolution_notes` |
| Encaminhamento/Fechamento | `medical_records.referral_closure` |
| Profissional responsavel | `medical_records.created_by` → `users` |
| Versao do prontuario | `medical_records.version` |
| Total de sessoes | Contagem |

**Filtros:** periodo, profissional, paciente
**Exportar:** PDF, Excel

---

### Prioridade Media (gestao e supervisao)

#### 1.5 Relatorio de Carga de Trabalho por Profissional
**Descricao:** Visao gerencial de cada profissional e seus pacientes.

| Campo | Fonte |
|-------|-------|
| Profissional (nome, especialidade) | `professionals` → `specialties` |
| Registro profissional | `professionals.registration_number` |
| Estagiario (sim/nao) | `professionals.is_intern` |
| Qtd de criancas atribuidas | `kid_professional` contagem |
| Lista de criancas | `kid_professional` → `kids` |
| Qtd de checklists realizados | `checklists` por profissional |
| Qtd de prontuarios | `medical_records` por profissional |
| Qtd de documentos gerados | `generated_documents` por profissional |
| Progresso medio dos pacientes | `OverviewService` agregado |

**Filtros:** especialidade, estagiario, periodo
**Exportar:** PDF, Excel

---

#### 1.6 Relatorio de Planos de Desenvolvimento
**Descricao:** Status dos planos de desenvolvimento ativos e concluidos.

| Campo | Fonte |
|-------|-------|
| Crianca | `planes.kid_id` → `kids` |
| Checklist de origem | `planes.checklist_id` → `checklists` |
| Nome do plano | `planes.name` |
| Status (ativo/inativo) | `planes.is_active` |
| Qtd de competencias no plano | `competence_plane` contagem |
| Competencias do plano | `competence_plane` → `competences` |
| Dominio de cada competencia | `competences.domain_id` → `domains` |
| Evolucao das competencias | Nota no checklist anterior vs atual |

**Filtros:** crianca, status do plano, dominio
**Exportar:** PDF, Excel

---

#### 1.7 Relatorio de Documentos Gerados
**Descricao:** Historico de documentos clinicos emitidos.

| Campo | Fonte |
|-------|-------|
| Tipo de documento | `generated_documents.model_type` (6 modelos) |
| Paciente | `generated_documents.documentable_type/id` |
| Profissional responsavel | `generated_documents.professional_id` |
| Gerado por | `generated_documents.generated_by` |
| Data de geracao | `generated_documents.generated_at` |
| Total por tipo | Contagem agrupada |

**Filtros:** tipo de documento, profissional, periodo, paciente
**Exportar:** PDF, Excel

---

### Prioridade Baixa (analise avancada e pesquisa)

#### 1.8 Relatorio de Analise Populacional (Coorte)
**Descricao:** Analise agregada do desempenho de todas as criancas.

| Campo | Fonte |
|-------|-------|
| Total de criancas | Contagem `kids` |
| Distribuicao por genero | `kids.gender` agrupado |
| Distribuicao por etnia | `kids.ethnicity` agrupado |
| Distribuicao por faixa etaria | Calculado por `birth_date` |
| Media de progresso geral | `OverviewService` agregado |
| Dominios com mais dificuldade | Menor % medio por dominio |
| Dominios com melhor desempenho | Maior % medio por dominio |
| Distribuicao de atraso (meses) | Histograma |
| Competencias mais falhadas | Nota 0/1 mais frequente |

**Filtros:** periodo, nivel, genero, etnia, faixa etaria
**Exportar:** PDF (com graficos), Excel

---

#### 1.9 Relatorio de Audit Trail (Log de Atividades)
**Descricao:** Registro de acoes realizadas no sistema para auditoria.

| Campo | Fonte |
|-------|-------|
| Objeto | `logs.object` |
| ID do objeto | `logs.object_id` |
| Acao (insert/update/remove/info) | `logs.action` |
| Descricao | `logs.description` |
| Data/hora | `logs.creation_date` |
| Usuario responsavel | `logs.created_by` → `users` |

**Filtros:** periodo, usuario, tipo de acao, objeto
**Exportar:** PDF, Excel

---

#### 1.10 Relatorio Comparativo entre Avaliacoes
**Descricao:** Comparacao lado a lado de dois ou mais checklists de uma crianca.

| Campo | Fonte |
|-------|-------|
| Crianca | `kids.*` |
| Checklist 1 vs Checklist 2 | `checklists` selecionados |
| % geral em cada checklist | `OverviewService` |
| Delta de progresso | Diferenca calculada |
| % por dominio em cada checklist | Agrupado por dominio |
| Dominios que melhoraram | Delta positivo |
| Dominios que pioraram | Delta negativo |
| Competencias que mudaram de nota | Comparacao note pivot |

**Filtros:** crianca, checklists a comparar
**Exportar:** PDF (com graficos radar), Excel

---

## 2. Formato dos Relatorios

### PDF
- Header com logo + nome da clinica + data de geracao
- Footer com paginacao + "Gerado por Maieutica"
- Tabelas com cores do design system (rosa #AD6E9B, cinzas)
- Graficos embutidos como imagem (Chart.js → base64)
- Fonte: DejaVu Sans (requisito DomPDF)
- Orientacao: retrato para listagens, paisagem para tabelas largas

### Excel
- Cabecalho com nome do relatorio + filtros aplicados + data
- Dados tabulares puros (sem formatacao excessiva)
- Uma aba por secao quando houver multiplos agrupamentos
- Pacote sugerido: `maatwebsite/excel` (Laravel Excel) - compativel com Laravel 9

---

## 3. Arquitetura Proposta

### Pacote para Excel
```bash
composer require maatwebsite/excel
```
- `maatwebsite/excel` ^3.1 - compativel com Laravel 9 e PHP 8.1
- Suporta exports, imports, queued exports (para relatorios grandes)

### Estrutura de arquivos
```
app/
  Http/Controllers/
    ReportController.php          # Controller principal de relatorios
  Exports/                        # Classes de exportacao Excel
    KidsReportExport.php
    ProgressReportExport.php
    ProfessionalCaseloadExport.php
    MedicalRecordsExport.php
    ...
  Services/
    ReportService.php             # Logica de consulta e agregacao
resources/views/
  reports/
    index.blade.php               # Menu de relatorios
    kids.blade.php                # Filtros + preview de criancas
    progress.blade.php            # Filtros + preview de progresso
    ...
    pdf/                          # Templates PDF
      kids-report.blade.php
      progress-report.blade.php
      ...
```

### Rota
```php
Route::prefix('reports')->middleware('auth')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/kids', [ReportController::class, 'kids'])->name('reports.kids');
    Route::get('/kids/export/{format}', [ReportController::class, 'kidsExport'])->name('reports.kids.export');
    Route::get('/progress/{kid}', [ReportController::class, 'progress'])->name('reports.progress');
    Route::get('/progress/{kid}/export/{format}', [ReportController::class, 'progressExport'])->name('reports.progress.export');
    // ... demais relatorios
});
```

### Menu no Sidebar
```blade
<div class="menu-item">
    <a class="menu-link has-submenu" data-submenu="submenu-relatorios">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Relatorios</span>
        <i class="bi bi-chevron-down submenu-arrow"></i>
    </a>
    <ul class="submenu" id="submenu-relatorios">
        <li><a href="..." class="submenu-link">Criancas</a></li>
        <li><a href="..." class="submenu-link">Progresso Individual</a></li>
        <li><a href="..." class="submenu-link">Desempenho por Dominio</a></li>
        <li><a href="..." class="submenu-link">Prontuarios</a></li>
        <li><a href="..." class="submenu-link">Profissionais</a></li>
        <li><a href="..." class="submenu-link">Planos de Desenvolvimento</a></li>
        <li><a href="..." class="submenu-link">Documentos Gerados</a></li>
    </ul>
</div>
```

---

## 4. Plano de Implementacao

### Fase 1 - Base (3-5 dias)
- Instalar `maatwebsite/excel`
- Criar `ReportController` e `ReportService`
- Criar pagina index de relatorios com menu
- Implementar **Relatorio de Criancas Cadastradas** (PDF + Excel)
- Adicionar menu "Relatorios" no sidebar

### Fase 2 - Clinico (5-7 dias)
- Implementar **Relatorio de Progresso Individual** (PDF com graficos + Excel)
- Implementar **Relatorio de Desempenho por Dominio** (PDF + Excel)
- Implementar **Relatorio de Prontuarios** (PDF + Excel)

### Fase 3 - Gestao (3-5 dias)
- Implementar **Relatorio de Carga por Profissional** (PDF + Excel)
- Implementar **Relatorio de Planos de Desenvolvimento** (PDF + Excel)
- Implementar **Relatorio de Documentos Gerados** (PDF + Excel)

### Fase 4 - Avancado (5-7 dias)
- Implementar **Relatorio de Analise Populacional** (PDF com graficos + Excel)
- Implementar **Relatorio Comparativo entre Avaliacoes** (PDF com radar + Excel)
- Implementar **Relatorio de Audit Trail** (PDF + Excel)

---

## 5. Permissoes Sugeridas

| Permissao | Descricao |
|-----------|-----------|
| `report-list` | Acessar menu de relatorios (proprios pacientes) |
| `report-list-all` | Acessar todos os relatorios (admin) |
| `report-export` | Exportar relatorios em PDF/Excel |

---

## 6. Dados ja Disponiveis vs Necessarios

| Relatorio | Dados disponiveis | Precisa implementar |
|-----------|-------------------|---------------------|
| Criancas cadastradas | 100% | Apenas controller + view |
| Progresso individual | 100% | Controller + view + graficos em PDF |
| Desempenho por dominio | 100% | Controller + view |
| Prontuarios | 100% | Controller + view |
| Carga por profissional | 95% | Query agregada |
| Planos de desenvolvimento | 100% | Controller + view |
| Documentos gerados | 100% | Controller + view |
| Analise populacional | 90% | Queries agregadas + graficos |
| Comparativo avaliacoes | 100% | Controller + view + radar |
| Audit trail | 100% | Controller + view |

> **Conclusao:** Todos os dados necessarios ja existem no banco. Nao e necessario criar novas tabelas ou migrations. O trabalho e de controller, views e exports.
