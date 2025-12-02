<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Declaração</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
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
            width: 140px;
        }

        /** FOOTER FIXO **/
        footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
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
            margin-bottom: 40px;
            position: relative;
            top: 30px;
        }

        .content {
            margin-top: 40px;
            text-align: justify;
            margin-bottom: 40px;
            position: relative;
            top: 60px;
        }

        .signature {
            text-align: center;
            margin-top: 80px;
            position: relative;
            top: 80px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 300px;
            margin-left: auto;
            margin-right: auto;
            padding-top: 5px;
        }

footer {
    position: fixed;
    bottom: -10px;
    left: 0;
    right: 0;
    height: 90px;
    text-align: left;     /* <-- AQUI MUDA TUDO */
    font-size: 11px;
    color: #444;
    line-height: 1.4;
    padding-left: 40px;   /* mesma margem do conteúdo */
}

.footer-line {
    margin-bottom: 4px;
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

            11 4554.4023</div>
        <div class="footer-line">
            <img src="{{ public_path('images/whatsapp.svg') }}"
                width="12"
                style="margin-right: 4px; position: relative; top: 1px;">

            +55 11 9 7543.9667</div>
        <div class="footer-line">
            <img src="{{ public_path('images/geo-alt-fill.svg') }}"
                width="12"
                style="margin-right: 4px; position: relative; top: 1px;">

            R. Prof. Edgar de Moraes, 168 — Jardim Frediani, Santana de Parnaíba/SP — CEP 06502-203
        </div>
    </footer>

    <div class="title">DECLARAÇÃO</div>

    <div class="content">
        <p>
            Declaro para os devidos fins que <strong>{{ $nome_paciente }}</strong>, está sendo submetido(a)
            a acompanhamento psicológico, sob meus cuidados profissionais, comparecendo às sessões
            {{ $dias_horarios }},
            nesta Clínica, até o presente momento sem data de previsão para o término do acompanhamento
            {{ $previsao_termino ? ', com previsão de término em ' . $previsao_termino : '' }}.
        </p>
    </div>

    <div class="signature">
        <div class="signature-line">
            {{ $nome_psicologo }}<br>
            CRP: {{ $crp }}
        </div>
    </div>

    <div style="text-align: right; margin-top: 400px;">
        {{ $cidade }}, {{ $data_formatada }}.
    </div>






</body>
</html>
