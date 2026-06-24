# Profissionais Ocultos — Auditoria

**Data:** 2026-06-24
**Motivo:** Solicitação da Flávia Moreno para ocultar profissionais nas telas e PDFs durante período de auditoria.
**Status:** Ocultação ativa. Para reverter, descomentar as linhas indicadas abaixo.

---

## Arquivos alterados

### 1. `resources/views/components/kid-info-card.blade.php`
**Alteração:** Adicionado prop `showProfessionals` (padrão `true`). Quando `false`, a seção "Profissionais Responsáveis" é ocultada no card da criança.

**Para reverter:** Remover a declaração `@props` e os `@if($showProfessionals)` / `@endif`.

---

### 2. `resources/views/kids/overview.blade.php`
**Alteração:** `<x-kid-info-card :kid="$kid" :showProfessionals="false" />`
**Tela afetada:** http://maieutica.test/kids/{id}/overview

**Para reverter:** Remover `:showProfessionals="false"`.

---

### 3. `resources/views/kids/radar_chart2.blade.php`
**Alteração:** `<x-kid-info-card :kid="$kid" :showProfessionals="false" />`
**Tela afetada:** http://maieutica.test/analysis/{id}/level/{level}

**Para reverter:** Remover `:showProfessionals="false"`.

---

### 4. `resources/views/checklists/index.blade.php`
**Alteração:** `<x-kid-info-card :kid="$kid" :showProfessionals="false" />`
**Tela afetada:** http://maieutica.test/checklists?kidId={id}

**Para reverter:** Remover `:showProfessionals="false"`.

---

### 5. `resources/views/checklists/fill.blade.php`
**Alteração:** `<x-kid-info-card :kid="$kid" :showProfessionals="false" />`
**Tela afetada:** http://maieutica.test/checklists/{id}/fill

**Para reverter:** Remover `:showProfessionals="false"`.

---

### 6. `app/Http/Controllers/KidsController.php` — método `generatePdf`
**Alteração:** Linha comentada no array `$infoLines` do PDF de Desenvolvimento:
```php
// 'Profissionais: '.implode(', ', $professionalNames),
```
**PDF afetado:** Relatório de Desenvolvimento (gerado em /kids/{id}/overview)

**Para reverter:** Descomentar a linha acima.

---

### 7. `app/Http/Controllers/KidsController.php` — método `preferences`
**Alteração:** Linhas comentadas na capa do Plano de Intervenção:
```php
// $pdf->SetFont('helvetica', '', 14);
// $pdf->Write(0, 'Profissional(ais)', '', 0, 'C', true, 0, false, false, 0);
// $pdf->Ln(5);
// $pdf->SetFont('helvetica', '', 12);
// $pdf->Write(0, $therapist, '', 0, 'C', true, 0, false, false, 0);
// $pdf->Ln(5);
```
**PDF afetado:** Capa do Plano de Intervenção (pdfPlane, pdfPlaneAuto, pdfPlaneAutoView)

**Para reverter:** Descomentar as linhas acima.

---

## Observações

- A lógica de busca dos profissionais no banco **não foi removida** — apenas a exibição foi ocultada.
- O PDF do Comparativo (`generateComparativoPdf`) nunca incluiu profissionais.
- O arquivo `docker/nginx/nginx.conf` também foi alterado nesta sessão para corrigir erro 413 (`client_max_body_size 40M`), sem relação com profissionais.
