<!-- resources/views/kids/domain_details.blade.php -->

@extends('layouts.app') @section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('home.index') }}">Home</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ url('/analysis/' . $kid->id . '/level/0') }}">Comparativo</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Detalhes do Domínio
        </li>
    </ol>
</nav>
@endsection @section('title') Domínio @endsection @section('breadcrumb-items')
<li class="breadcrumb-item">
    <a href="{{ url('/analysis/' . $kid->id . '/level/0') }}">Comparativo</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    <i class="bi bi-eye"></i> Visão Geral
</li>
@endsection @section('content')
<div class="row">
    <div class="col-md-12">
        <x-kid-info-card
            :kid="$kid"
            :responsible="$kid->responsible"
            :professional="$kid->professional"
            :checklist-count="$kid->checklists()->count()"
            :plane-count="$kid->planes()->count()"
        />
    </div>
</div>

<div class="row">
    <div class="col-md-12 mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">{{ $domain->name }} ({{ $domain->initial }})</h2>
            <div class="d-flex align-items-center">
                <h2 class="mb-0 me-3">
                    {{ $levelId === 0 ? "Todos os níveis" : "Nível ".$levelId }}
                </h2>
                <a
                    href="{{ route('kids.radarChart2', ['kidId' => $kid->id, 'levelId' => $levelId]) }}"
                    class="btn btn-secondary"
                    ><i class="bi bi-arrow-left"></i> Voltar</a
                >
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mt-3">
        <div class="card mt-3">
            <div
                class="card-header d-flex justify-content-between align-items-center"
            >
                <h5 class="card-title mb-0">
                    Gráfico de Barras - Comparativo de Competências
                </h5>
                <div>
                    @if ($currentChecklist)
                    <small class="text-muted me-3">
                        <strong>Checklist Atual:</strong>
                        {{ $currentChecklist->name ?? 'Checklist ' .
                        $currentChecklist->id }}
                        -
                        {{ $currentChecklist->created_at->format('d/m/Y') }}
                    </small>
                    @endif @if ($previousChecklist)
                    <small class="text-muted">
                        <strong>Checklist de Comparação:</strong>
                        {{ $previousChecklist->name ?? 'Checklist ' . $previousChecklist->id }}
                        -
                        {{ $previousChecklist->created_at->format('d/m/Y') }}
                    </small>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div
                    class="chart-container d-flex justify-content-center align-items-center"
                >
                    <canvas id="barChartCompetences"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mt-3">
        <div class="d-flex justify-content-between">
            <h3>Habilidades e Percentis</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <td></td>
                        <th>Competência</th>
                        <th nowrap>Status Atual</th>
                        <th nowrap>Status Anterior</th>
                        <th nowrap>Progresso Percentil</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($radarDataCompetences as $competenceData)
                    <tr>
                        <td nowrap>
                            {{ $competenceData["domain_initial"] }} Nível:
                            {{ $competenceData["level"] }} Item:
                            {{ $competenceData["competence"] }}
                        </td>
                        <td>{{ $competenceData["description"] ?? "" }}</td>
                        <td nowrap>
                            @if ($competenceData['currentStatusValue'] === 1)
                            Desenvolvido
                            @elseif($competenceData['currentStatusValue'] === 2)
                            Em desenvolvimento
                            @elseif($competenceData['currentStatusValue'] === 3)
                            Não desenvolvido @else Não Avaliado @endif
                        </td>
                        <td nowrap>
                            @if ($competenceData['previousStatusValue'] === 1)
                            Desenvolvido
                            @elseif($competenceData['previousStatusValue'] ===
                            2) Em desenvolvimento
                            @elseif($competenceData['previousStatusValue'] ===
                            3) Não desenvolvido @else Não Avaliado @endif
                        </td>
                        <td>
                            <!-- Barra de Progresso -->
                            <div class="progress">
                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    style="width: {{
                                        $competenceData['percentComplete'] ??
                                            100
                                    }}%; background-color: {{
                                        $competenceData['statusColor']
                                    }};"
                                    aria-valuenow="{{
                                        $competenceData['percentComplete'] ??
                                            100
                                    }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                >
                                    {{ $competenceData["status"] }}
                                </div>
                            </div>
                        </td>
                        <td nowrap>
                            <canvas
                                id="percentilChart-{{ $loop->index }}"
                                width="400"
                                height="200"
                            ></canvas>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection @push('scripts')
<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Dados para o Gráfico de Radar
    //var ctxRadarCompetences = document.getElementById('radarChartCompetences').getContext('2d');
    //var radarLabelsCompetences = @json(array_column($radarDataCompetences, 'competence'));
    // Concatenar 'domain_initials' com 'competence' para os rótulos
    var radarLabelsCompetences = @json(array_map(function ($item) {
        return $item['domain_initial']. ' Nível: '.$item['level']. ' Item: '.$item['description'];
    }, $radarDataCompetences));


    var radarDataCurrent = @json(array_map(function ($item) {
        return $item['currentStatusValue'] ?? 0;
    }, $radarDataCompetences));

    var radarDataPrevious = @json(array_map(function ($item) {
        return $item['previousStatusValue'] ?? 0;
    }, $radarDataCompetences));

    var datasets = [];

    @if ($currentChecklist)
        datasets.push({
            label: 'Checklist Atual - {{ $currentChecklist->created_at->format('d/ m / Y') }}',
            data: radarDataCurrent,
            backgroundColor: 'rgba(54, 162, 235, 0.2)', // Cor do preenchimento
            borderColor: 'rgba(54, 162, 235, 1)', // Cor da linha
            borderWidth: 1
            });
    @endif

    @if ($previousChecklist)
        datasets.push({
            label: 'Checklist de Comparação - {{ $previousChecklist->created_at->format('d/ m / Y') }}',
            data: radarDataPrevious,
            backgroundColor: 'rgba(255, 99, 132, 0.2)', // Cor do preenchimento
            borderColor: 'rgba(255, 99, 132, 1)', // Cor da linha
            borderWidth: 1
            });
    @endif


    // Dados da idade da criança passado pelo backend
    document.addEventListener('DOMContentLoaded', function () {
        @foreach($radarDataCompetences as $competenceData)
        const ctx{{ $loop-> index
    }} = document.getElementById('percentilChart-{{ $loop->index }}')
        .getContext('2d');
    new Chart(ctx{{ $loop-> index }}, {
        type: 'bar',
        data: {
            labels: ['25%', '50%', '75%', '90%'], // Percentis no eixo X
            datasets: [{
                label: 'Percentis',
                data: [{{ $competenceData['percentil_25'] }},
            {{ $competenceData['percentil_50'] }},
                                    {{ $competenceData['percentil_75'] }},
        {{ $competenceData['percentil_90'] }}
                                ],
        backgroundColor: 'rgba(255, 159, 64, 0.6)',
        borderColor: 'rgba(255, 159, 64, 1)',
        borderWidth: 1
                            },
        {
            label: 'Idade da Criança',
            data: [
                {{ $ageInMonths<$competenceData['percentil_50']? $ageInMonths : 'null' }},
        {{ $ageInMonths >= $competenceData['percentil_50'] && $ageInMonths < $competenceData['percentil_75'] ? $ageInMonths : 'null' }},
        {{ $ageInMonths >= $competenceData['percentil_75'] && $ageInMonths < $competenceData['percentil_90'] ? $ageInMonths : 'null' }},
        {{ $ageInMonths >= $competenceData['percentil_90'] ? $ageInMonths : 'null' }}
                                ],
        type: 'bar', // Definimos como linha ou ponto
        borderColor: 'rgba(54, 162, 235, 1)',
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
        fill: true,
        borderWidth: 2,
        pointRadius: 5, // Aumenta o tamanho do ponto
        pointBackgroundColor: 'rgba(54, 162, 235, 1)', // Cor do ponto
        maxBarThickness: 40 // Aumenta a espessura da barra
                            }
                        ]
                    },
        options: {
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Idade (meses)'
                },
                ticks: {
                    callback: function (value) {
                        return value + ' meses'; // Exibe a idade em meses no eixo Y
                    },
                    stepSize: 1, // Define um passo de 5 meses para o eixo Y
                    max: Math.max({{ $competenceData['percentil_90'] }},
                                            {{ $ageInMonths }}) +
        10 // Ajusta o limite máximo do gráfico para garantir que a linha da idade caiba
                                }
                            },
    x: {
        title: {
            display: true,
                text: 'Percentis'
        }
    }
                        }
                    }
                });
    @endforeach
        });








    // Configuração do novo gráfico de barras
    var ctxBarCompetences = document.getElementById('barChartCompetences').getContext('2d');

    var barLabelsCompetences = @json(array_map(function ($item) {
        return $item['description'];
    }, $radarDataCompetences));

    var barDataCurrent = @json(array_map(function ($item) {
        return $item['currentStatusValue'] ?? 0;
    }, $radarDataCompetences));

    var barDataPrevious = @json(array_map(function ($item) {
        return $item['previousStatusValue'] ?? 0;
    }, $radarDataCompetences));

    var barDatasets = [];

    @if ($currentChecklist)
        barDatasets.push({
            label: 'Checklist Atual - {{ $currentChecklist->created_at->format("d/m/Y") }}',
            data: barDataCurrent,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        });
    @endif

    @if ($previousChecklist)
        barDatasets.push({
            label: 'Checklist Anterior - {{ $previousChecklist->created_at->format("d/m/Y") }}',
            data: barDataPrevious,
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        });
    @endif


    // Ajustar altura das barras e altura do gráfico

    /// Calcular a altura dinâmica com base no número de barras
    var numberOfBars = barLabelsCompetences.length;
    var baseHeightPerBar = 40; // Altura base por barra (em pixels)
    var dynamicHeight = numberOfBars * baseHeightPerBar;

    // Ajustar a altura do canvas
    var canvas = document.getElementById('barChartCompetences');
    canvas.style.height = dynamicHeight + 'px';

    new Chart(ctxBarCompetences, {
        type: 'bar',
        data: {
            labels: barLabelsCompetences,
            datasets: barDatasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Muda para eixo Y
            scales: {
                x: { // Agora é o eixo X que tem os status
                    beginAtZero: true,
                    max: 3,
                    ticks: {
                        stepSize: 1,
                        callback: function (value) {
                            if (value === 0) return 'Não Avaliado';
                            if (value === 1) return 'Não Desenvolvido';
                            if (value === 2) return 'Em Desenvolvimento';
                            if (value === 3) return 'Desenvolvido';
                            return value;
                        }
                    }
                },
                y: { // Agora é o eixo Y que tem as descrições
                    ticks: {
                        autoSkip: false,
                        maxRotation: 0,
                        minRotation: 0
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.x !== null) { // Mudado de y para x
                                label += ['Não Avaliado', 'Não Desenvolvido',
                                    'Em Desenvolvimento', 'Desenvolvido'][context.parsed.x];
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
