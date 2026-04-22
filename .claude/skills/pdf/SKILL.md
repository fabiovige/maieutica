---
description: Geração de PDFs (TCPDF + DomPDF), regra de download forçado, nomenclatura, rotas e logging
---

Use esta skill sempre que for criar, modificar ou revisar qualquer geração de PDF no sistema.

## Bibliotecas em uso

| Biblioteca | Quando usar | Classe base | Onde vive |
|---|---|---|---|
| **TCPDF** | PDFs programáticos (gráficos, tabelas desenhadas com coordenadas, overviews) | `App\Util\MyPdf` (extends TCPDF) | `KidsController` |
| **DomPDF** | PDFs baseados em template Blade/HTML (declarações, laudos, pareceres, prontuários) | `Barryvdh\DomPDF\Facade\Pdf` | `DocumentsController`, `MedicalRecordsController`, `UserController` |

Para escolher: se o conteúdo é um documento textual estruturado → DomPDF com Blade. Se precisa desenhar coordenadas, charts ou layouts complexos → TCPDF.

## REGRA INEGOCIÁVEL — sempre forçar download

**Nunca** retornar PDF inline (`stream()` no DomPDF, `'I'` no TCPDF). Motivos:

1. **F5 em rota POST quebra** (`The GET method is not supported`) — ver histórico em `KidsController@generatePdf`
2. **Consumo desnecessário** — se o usuário não quer baixar, é sinal que a UI deveria mostrar os dados em HTML, não PDF
3. **UX consistente** — o usuário sempre espera o arquivo salvo
4. **Regeneração acidental** — rotas GET com inline permitem refresh infinito, desperdiçando CPU

## Padrão canônico de saída

### DomPDF (Blade → PDF)

```php
use Barryvdh\DomPDF\Facade\Pdf;

$html = view('documents.modeloN', compact(...))->render();
$pdf  = Pdf::loadHTML($html)->setPaper('A4', 'portrait');

return $pdf->download($filename); // ✅ SEMPRE download, NUNCA stream
```

### TCPDF (desenho programático)

```php
use App\Util\MyPdf;

$pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->AddPage();
// ...desenha o conteúdo...

return response($pdf->Output($filename, 'S'), 200)
    ->header('Content-Type', 'application/pdf')
    ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
```

**Nunca** usar `$pdf->Output($name, 'I')` ou `$pdf->Output($name, 'D')` (`D` também serve mas é mais consistente retornar `response()` com headers explícitos, que funciona em qualquer método HTTP).

## Nomenclatura de arquivos

Sempre `slug` + timestamp no nome:

```php
$safeName = \Illuminate\Support\Str::slug($kid->name, '-');
$filename = 'relatorio-desenvolvimento_'.$safeName.'_'.now()->format('Ymd-His').'.pdf';
```

Padrões existentes:
- `relatorio-desenvolvimento_{slug}_{Ymd-His}.pdf` — overview TCPDF
- `{tipo-doc}_modelo_{N}.pdf` — documentos DomPDF (ex: `declaracao_modelo_1.pdf`)
- `{nome-kid}_{dmY}_{plane_id}.pdf` — planos

Evite: nomes hardcoded sem contexto do paciente, caracteres especiais (acentos, espaços, `/`).

## Rotas: GET × POST

- **GET** — PDF sem side-effects, só lê dados e gera output. Safe para F5 (mas ainda assim: forçar download para não cachear conteúdo renderizado).
- **POST** — obrigatório quando o controller recebe imagens de charts geradas client-side (base64 no body), muda estado ou tem payload grande. Ex: `kids.generatePdf` recebe `barChartImage`/`radarChartImage` do frontend.

Ao criar nova rota, escolha GET por padrão. Só use POST se precisar enviar payload não-idempotente.

## Templates Blade para DomPDF

**Obrigatório:**
- Estender `documents.layouts.pdf-base` (estilos compartilhados)
- Fonte `DejaVu Sans` (único suporte completo a acentos no DomPDF)
- CSS inline (DomPDF não suporta `@import`, `rel="stylesheet"` externo)
- Imagens em base64 ou com caminho absoluto do filesystem (`public_path(...)`)

Localização: `resources/views/documents/` (modelos), `resources/views/medical-records/pdf-template.blade.php`, `resources/views/users/show.blade.php` (ficha).

Após alterar template: `docker compose exec app php artisan view:clear`.

## Permissões

| Permission | Quem |
|---|---|
| `document-list`, `document-show`, `document-download`, `document-delete` | Profissional |
| `document-list-all`, `document-show-all`, `document-delete-all` | Admin |
| `checklist-plane-automatic` | Profissional com acesso a plano automático |

Sempre verificar via `can()` ou `$this->authorize(...)` antes de gerar — PDFs podem conter dados sensíveis (LGPD).

## Logging

Toda falha na geração deve ser logada pelo Domain Logger do contexto:

```php
try {
    // geração...
} catch (\Exception $e) {
    $this->kidLogger->pdfGenerationFailed($kid, 'overview', $e, [...]);
    flash('Não foi possível gerar o PDF.')->error();
    return redirect()->back();
}
```

Sucesso geralmente não precisa de log específico além do Observer padrão.

## Mapa atual de geradores

| Arquivo:linha | Método | Tipo | Rota |
|---|---|---|---|
| `KidsController.php:~610` | `pdfPlane()` | TCPDF | `kids.pdfplane` (GET) |
| `KidsController.php:~746` | `pdfPlaneAuto()` | TCPDF | `kids.pdfplaneauto` (GET) |
| `KidsController.php:~856` | `pdfPlaneAutoView()` | TCPDF | `kids.pdfplaneautoview` (GET) |
| `KidsController.php:~1802` | `generatePdf()` | TCPDF | `kids.generatePdf` (POST) |
| `DocumentsController.php:155/205/318/453/575/650` | `modelo1..6()` | DomPDF | `documentos.modeloN` (POST) |
| `DocumentsController.php:701` | `download()` | DomPDF | `documentos.download` (GET) |
| `MedicalRecordsController.php:598` | `downloadPdf()` | DomPDF | `medical-records.pdf` (GET) |
| `UserController.php:428` | `pdf()` | DomPDF | `users.pdf` (GET) |

Ao adicionar novo PDF, atualize esta tabela.

## Checklist ao criar novo PDF

- [ ] Escolheu biblioteca correta (TCPDF para desenho, DomPDF para Blade)?
- [ ] Autorização via `can()` / `authorize()` antes da geração?
- [ ] Output força download (`download()` / `Content-Disposition: attachment`)?
- [ ] Nome de arquivo com `Str::slug` + timestamp?
- [ ] Try/catch com Domain Logger no erro?
- [ ] Se DomPDF: template estende `documents.layouts.pdf-base` e usa `DejaVu Sans`?
- [ ] Adicionou na tabela de mapa acima?

Para documentos (regras de domínio), veja `/documentos`. Para logging, veja `/logging`. Para permissões, veja `/auth`.
