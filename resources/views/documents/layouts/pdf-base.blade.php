<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('document-title', 'Documento')</title>

    <style>
        /* =============================================
           PDF Tipografia - Escala alinhada ao sistema
           DejaVu Sans obrigatório para DomPDF
           ============================================= */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;        /* base alinhado com --fs-base (0.875rem ≈ 14px) */
            line-height: 1.5;       /* alinhado com --lh-normal */
            color: #212529;         /* alinhado com --text-body */
            margin: 80px 40px 60px 40px;
        }

        /* Escala tipográfica para PDF (px pois DomPDF não suporta rem) */
        .pdf-fs-xs   { font-size: 11px; }  /* ~--fs-xs captions, rodapé */
        .pdf-fs-sm   { font-size: 12px; }  /* ~--fs-sm referências, notas */
        .pdf-fs-base { font-size: 14px; }  /* ~--fs-base corpo de texto */
        .pdf-fs-md   { font-size: 16px; }  /* ~--fs-md destaque */

        /* Seções de documento (h3 nos modelos) */
        .pdf-section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .pdf-section-title:first-of-type {
            margin-top: 20px;
        }

        /* Texto de conteúdo */
        .pdf-text {
            text-align: justify;
        }

        /* Nota/instrução (texto menor em itálico) */
        .pdf-note {
            font-size: 11px;
            font-style: italic;
            margin-bottom: 10px;
        }

        /* Referências */
        .pdf-reference {
            font-size: 12px;
            text-align: justify;
        }

        /** HEADER FIXO **/
        header {
            position: fixed;
            top: -40px;
            left: 0;
            right: 0;
            height: 60px;
            text-align: center;
        }

        header img {
            width: 240px;
        }

        /** FOOTER FIXO **/
        footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 90px;
            text-align: left;
            font-size: 11px;
            color: #444;
            line-height: 1.4;
            padding-left: 40px;
        }

        .footer-line {
            margin-bottom: 4px;
        }

        /** MARCA D'ÁGUA **/
        .watermark {
            position: fixed;
            top: 75%;
            left: 40%;
            opacity: 0.60;
            transform: translate(-50%, -50%);
            width: 850px;
            z-index: -1;
        }

        .title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .content {
            text-align: justify;
            margin-bottom: 10px;
        }

        /* Página exclusiva de assinatura */
        .signature-page {
            page-break-before: always;
            position: relative;
            height: 580px;
        }

        .signature-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .signature {
            text-align: center;
            margin-bottom: 30px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 300px;
            margin-left: auto;
            margin-right: auto;
            padding-top: 5px;
        }

        .date-location {
            text-align: right;
        }

        @yield('pdf-styles')
    </style>

</head>
<body>

    {{-- MARCA D'ÁGUA --}}
    <img class="watermark" src="data:image/png;base64,{{ $watermark }}" alt="watermark">

    {{-- HEADER --}}
    <header>
        <img src="data:image/png;base64,{{ $logo }}" alt="logo">
    </header>

    {{-- FOOTER --}}
    <footer>
        <div class="footer-line">
            <img src="{{ public_path('images/globe.svg') }}"
                width="12"
                style="margin-right: 4px; position: relative; top: 1px;">
            www.clinicamaieutica.com.br
        </div>
        <div class="footer-line">
            <img src="{{ public_path('images/telephone-fill.svg') }}"
                width="12"
                style="margin-right: 4px; position: relative; top: 1px;">
            11 4554.4023
        </div>
        <div class="footer-line">
            <img src="{{ public_path('images/whatsapp.svg') }}"
                width="12"
                style="margin-right: 4px; position: relative; top: 1px;">
            +55 11 9 7543.9667
        </div>
        <div class="footer-line">
            <img src="{{ public_path('images/geo-alt-fill.svg') }}"
                width="12"
                style="margin-right: 4px; position: relative; top: 1px;">
            R. Prof. Edgar de Moraes, 168 — Jardim Frediani, Santana de Parnaíba/SP — CEP 06502-203
        </div>
    </footer>

    {{-- TÍTULO DO DOCUMENTO --}}
    <div class="title">
        @yield('title', 'DECLARAÇÃO')
    </div>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="content">
        @yield('content')
    </div>

    {{-- PÁGINA EXCLUSIVA DE ASSINATURA — sempre a última página --}}
    <div class="signature-page">
        <div class="signature-content">
            <div class="signature">
                @hasSection('signature')
                    @yield('signature')
                @else
                    <div class="signature-line">
                        {{ $nome_psicologo }}<br>
                        {{ $council ?? 'Reg.' }}: {{ $crp }}
                    </div>
                @endif
            </div>

            @hasSection('date-location')
                @yield('date-location')
            @else
                <div class="date-location">
                    {{ $cidade }}, {{ $data_formatada }}.
                </div>
            @endif
        </div>
    </div>

</body>
</html>
