@extends('documents.layouts.pdf-base')

@section('document-title', 'Laudo Psicológico - Modelo 3')

@section('title')
    LAUDO PSICOLÓGICO
@endsection

@section('content')
    <h3 style="margin-top: 20px; margin-bottom: 15px; font-size: 14px;">1. IDENTIFICAÇÃO</h3>
    <p style="margin-bottom: 5px;"><strong>Nome:</strong> {{ $nome_paciente }}</p>
    <p style="margin-bottom: 5px;">
        <strong>Idade:</strong> {{ $idade ?? 'Não informada' }}
        <span style="margin-left: 40px;"><strong>Sexo:</strong> {{ $sexo ?? 'Não informado' }}</span>
    </p>
    <p style="margin-bottom: 5px;"><strong>Solicitante:</strong> {{ $solicitante ?? 'Não informado' }}</p>
    <p style="margin-bottom: 5px;"><strong>Finalidade:</strong> {{ $finalidade ?? 'Avaliação psicológica' }}</p>

    @if(isset($professionals) && count($professionals) > 0)
    <p style="margin-bottom: 5px;"><strong>Profissionais Envolvidos:</strong></p>
    <ul style="margin-top: 5px; margin-bottom: 15px; padding-left: 20px;">
        @foreach($professionals as $prof)
        <li style="margin-bottom: 3px;">{{ $prof['name'] }} - CRP {{ $prof['crp'] }}</li>
        @endforeach
    </ul>
    @else
    <p style="margin-bottom: 15px;"><strong>Autor(a):</strong> {{ $nome_psicologo }} <strong>Nº de Inscrição no CRP:</strong> {{ $crp }}</p>
    @endif

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">2. DESCRIÇÃO DA DEMANDA</h3>
    <p style="text-align: justify;">
        Os dados coletados na primeira entrevista foram relatados pelo(a) {{ $nome_informante ?? 'responsável' }}.
        As informações fornecidas pela descrição dos sintomas {{ $sintomas ?? '[descrever os sintomas relatados]' }},
        e as consequências negativas {{ $consequencias ?? '[descrever as consequências nas relações sociais e trabalho]' }}
        indicam que {{ $hipotese_diagnostico ?? '[descrever a hipótese ou diagnóstico]' }} vivido pelo(a) {{ $nome_paciente }}
        assumiu proporções impeditivas na sua vida. Estudos recentes apresentados em várias publicações têm indicado serem
        {{ $hipotese_diagnostico ?? '[descrever a hipótese ou diagnóstico]' }} os mais frequentes encontrados na população geral.
        De acordo com algumas características, eles são classificados como quadro patológico, cuja evolução, comprometimento
        e complicações ensejam busca de tratamento medicamentoso e/ou psicológico.
    </p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">3. PROCEDIMENTOS</h3>
    <p style="text-align: justify;">
        Foram realizadas entrevistas e aplicação de testes psicológicos em {{ $numero_encontros ?? '[X]' }} encontros
        de {{ $duracao_horas ?? '[X]' }} horas de duração em dias alternados.
        {{ $procedimentos_texto ?? 'Apresentar os recursos técnicos científicos utilizados especificando o referencial teórico metodológico que fundamentou sua análise, interpretação e conclusão. Citar as pessoas ouvidas no processo de trabalho desenvolvido, as informações, números de encontros ou tempo de duração.' }}
    </p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">4. ANÁLISE</h3>
    <p style="text-align: justify;">
        Nas sessões o(a) examinado(a) demonstrou {{ $analise_texto ?? 'descrever as principais características e evolução do trabalho realizado, sem corresponder a uma descrição literal das sessões. As informações devem ser sustentadas em fatos e teorias respeitando a fundamentação teórica e o instrumental técnico utilizado.' }}
    </p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">5. CONCLUSÃO</h3>
    <p style="text-align: justify;">
        De acordo com o Manual de Diagnóstico e Estatística de Distúrbios Mentais especialmente no capítulo que trata
        dos Distúrbios de {{ $diagnostico ?? '[descrever o diagnóstico]' }}, os sintomas apresentados pelo(a)
        {{ $nome_paciente }} caracterizam um quadro compatível com a descrição de
        {{ $sintoma_principal ?? '[descrever o sintoma]' }} cujo diagnóstico referido no Código Internacional de Doenças (CID)
        recebe a sigla {{ $cid ?? '[descrever o CID]' }}. A evolução deste distúrbio tem sido habitualmente crônica,
        sendo exacerbado quando a pessoa enfrenta as situações que desencadeiam o medo. Raramente este distúrbio torna
        o acometido incapaz; entretanto em muitos casos em função do evitamento da situação, ele chega a interferir nas
        relações sociais e no avanço profissional, comprometendo assim o paciente.
    </p>
    <p style="text-align: justify;">
        Diante dos dados colhidos na primeira entrevista com o(a) {{ $nome_paciente }}, e considerando que os sintomas
        relatados levam o(a) referido(a) à vivência de sofrimentos subjetivos e considerando que os mesmos estão
        comprometendo sua qualidade de vida pessoal e profissional, apontando para a possibilidade de complicações maiores,
        conclui-se, como terapêutica preventiva dessa evolução para remissão total ou parcial dos sintomas, a necessidade
        urgente de acompanhamento psicológico.
    </p>
    <p style="text-align: justify; margin-top: 15px;">
        <em>Declaro ainda que este documento não poderá ser utilizado para fins diferentes da sua finalidade pois
        trata-se de documento sigiloso e extrajudicial.</em>
    </p>
    <p style="text-align: justify; margin-top: 10px; font-size: 12px; font-style: italic;">
        (Na conclusão indicam-se os encaminhamentos e intervenções, o diagnóstico, prognóstico e hipótese diagnóstica,
        a evolução do caso, orientação ou sugestão de projeto terapêutico)
    </p>

    @if($referencias ?? false)
    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">6. REFERÊNCIAS</h3>
    <p style="font-size: 12px; text-align: justify;">
        {!! nl2br(e($referencias)) !!}
    </p>
    @endif
@endsection
