<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentTemplate;

// Converter imagens para base64
$bgDoc = base64_encode(file_get_contents('public/images/bg-doc.png'));
$logo = base64_encode(file_get_contents('public/images/logo_login_transparente.png'));

// Template com marca d'água DEPOIS do conteúdo
$html = <<<HTML
<style>
.header {
    text-align: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #0066cc;
    padding-bottom: 10px;
}

h1 {
    text-align: center;
    font-size: 18pt;
    font-weight: bold;
    margin: 40px 0;
}

p {
    text-align: justify;
    font-size: 12pt;
    line-height: 1.8;
    margin: 20px 0;
}

.signature {
    margin-top: 80px;
    text-align: center;
}

.signature-line {
    border-top: 1px solid #000;
    width: 300px;
    margin: 0 auto;
    padding-top: 5px;
}

.date {
    margin-top: 50px;
    text-align: left;
}
</style>

<!-- Header com logo -->
<div class="header">
    <img src="data:image/png;base64,{$logo}" height="70">
</div>

<h1>DECLARAÇÃO</h1>

<p>Declaro para os devidos fins que <strong>{{nome_completo}}</strong> e/ou <strong>{{nome_acompanhante}}</strong>, está sendo submetida a acompanhamento psicológico, sob meus cuidados profissionais, comparecendo às sessões {{dias_semana}}, {{horario_atendimento}}, nesta Clínica, até o presente momento sem data de previsão para o término do acompanhamento.</p>

<div class="signature">
    <div class="signature-line">
        {{profissional_nome}}<br>
        {{profissional_registro}}
    </div>
</div>

<div class="date">
    <p>Santana de Parnaíba, {{data_emissao}}.</p>
</div>
HTML;

$template = DocumentTemplate::find(1);
$template->html_content = $html;
$template->save();

echo "✅ Template atualizado SEM marca d'água no HTML!\n";
echo "A marca d'água será adicionada via Image() do TCPDF\n";
echo "DEPOIS do conteúdo ser renderizado\n";
