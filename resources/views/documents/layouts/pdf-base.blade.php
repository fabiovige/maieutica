<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('document-title', 'Documento')</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            margin: 80px 40px 60px 40px;
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
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .content {
            text-align: justify;
            margin-bottom: 40px;
        }

        .signature {
            text-align: center;
            margin-top: 40px;
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
            margin-top: 40px;
        }
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

    {{-- ASSINATURA --}}
    <div class="signature">
        @hasSection('signature')
            @yield('signature')
        @else
            <div class="signature-line">
                {{ $nome_psicologo }}<br>
                CRP: {{ $crp }}
            </div>
        @endif
    </div>

    {{-- DATA E LOCALIZAÇÃO --}}
    @hasSection('date-location')
        @yield('date-location')
    @else
        <div class="date-location">
            {{ $cidade }}, {{ $data_formatada }}.
        </div>
    @endif

</body>
</html>
