<!-- resources/views/kids/domain_details.blade.php -->

@extends('layouts.app')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kids.radarChart2', ['kidId' => $kid->id, 'levelId' => $levelId, $previousChecklist->id]) }}">Domínios</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detalhes do Domínio</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="col-md-12">
                <div class="d-flex justify-content-between">
                    <h2>{{ $domain->name }} ({{ $domain->initial }})</h2>
                    <h2>Nível {{ $levelId }}</h2>
                </div>            
            </div>

    <div class="row" id="app">
        <div class="row">    
            <div class="col-md-5">

                <Resume
                    :responsible="{{ $kid->responsible()->first() }}"
                    :professional="{{ $kid->professional()->first() }}"
                    :kid="{{ $kid }}"
                    :checklist="{{ $kid->checklists()->count() }}"
                    :plane="{{ $kid->planes()->count() }}"
                >
                </Resume>

                @if($currentChecklist)
                <p><strong>Checklist Atual:</strong> {{ $currentChecklist->name ?? 'Checklist ' . $currentChecklist->id }} - {{ $currentChecklist->created_at->format('d/m/Y') }}</p>
                @else
                <p><strong>Checklist Atual:</strong> Não disponível</p>
                @endif

                @if($previousChecklist)
                <p><strong>Checklist de Comparação:</strong> {{ $previousChecklist->name ?? 'Checklist ' . $previousChecklist->id }} - {{ $previousChecklist->created_at->format('d/m/Y') }}</p>
                @else
                <p><strong>Checklist de Comparação:</strong> Não disponível</p>
                @endif
            </div>
            <div class="col-md-7">
                <canvas id="radarChartCompetences" width="200" height="200"></canvas>
            </div>            
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between">  
            <h3>Habilidades e Percentis</h3>
            <a href="{{ route('kids.radarChart2', ['kidId' => $kid->id, 'levelId' => $levelId, $previousChecklist->id]) }}" class="btn btn-secondary">Voltar</a>
            </div>
            
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Competência</th>
                        <th nowrap>Status Atual</th>
                        <th nowrap>Status Anterior</th>
                        <th nowrap>Progresso Percentil</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($radarDataCompetences as $competenceData)
                    <tr>
                        <td>{{ $competenceData['competence'] }}</td>
                        <td>{{ $competenceData['description'] ?? '' }}</td>
                        <td nowrap>
                            @if($competenceData['currentStatusValue'] === 1)
                            Incapaz
                            @elseif($competenceData['currentStatusValue'] === 2)
                            Em Processo
                            @elseif($competenceData['currentStatusValue'] === 3)
                            Adquirido
                            @else
                            Não Avaliado
                            @endif
                        </td>
                        <td nowrap>
                            @if($competenceData['previousStatusValue'] === 1)
                            Incapaz
                            @elseif($competenceData['previousStatusValue'] === 2)
                            Em Processo
                            @elseif($competenceData['previousStatusValue'] === 3)
                            Adquirido
                            @else
                            Não Avaliado
                            @endif
                        </td>
                        <td>
                            <!-- Barra de Progresso -->
                            <div class="progress">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ $competenceData['percentComplete'] ?? 100 }}%; background-color: {{ $competenceData['statusColor'] }};"
                                    aria-valuenow="{{ $competenceData['percentComplete'] ?? 100 }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $competenceData['status'] }}
                                </div>
                            </div>
                        </td>
                        <td nowrap>
                            <canvas id="percentilChart-{{ $loop->index }}" width="400" height="200"></canvas>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>



@endsection


@push('scripts')

<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Dados para o Gráfico de Radar
    var ctxRadarCompetences = document.getElementById('radarChartCompetences').getContext('2d');
    var radarLabelsCompetences = @json(array_column($radarDataCompetences, 'competence'));

    var radarDataCurrent = @json(array_map(function($item) {
        return $item['currentStatusValue'] ?? 0;
    }, $radarDataCompetences));

    var radarDataPrevious = @json(array_map(function($item) {
        return $item['previousStatusValue'] ?? 0;
    }, $radarDataCompetences));

    var datasets = [];

    @if($currentChecklist)
    datasets.push({
        label: 'Checklist Atual - {{ $currentChecklist->created_at->format("d/m/Y") }}',
        data: radarDataCurrent,
        backgroundColor: 'rgba(54, 162, 235, 0.2)', // Cor do preenchimento
        borderColor: 'rgba(54, 162, 235, 1)', // Cor da linha
        borderWidth: 1
    });
    @endif

    @if($previousChecklist)
    datasets.push({
        label: 'Checklist de Comparação - {{ $previousChecklist->created_at->format("d/m/Y") }}',
        data: radarDataPrevious,
        backgroundColor: 'rgba(255, 99, 132, 0.2)', // Cor do preenchimento
        borderColor: 'rgba(255, 99, 132, 1)', // Cor da linha
        borderWidth: 1
    });
    @endif

    var radarChartCompetences = new Chart(ctxRadarCompetences, {
        type: 'radar',
        data: {
            labels: radarLabelsCompetences,
            datasets: datasets
        },
        options: {
            scales: {
                r: {
                    suggestedMin: 0,
                    suggestedMax: 3,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            if (value === 0) return 'Não Avaliado';
                            if (value === 1) return 'Incapaz';
                            if (value === 2) return 'Em Processo';
                            if (value === 3) return 'Adquirido';
                            return value;
                        }
                    }
                }
            }
        }
    });

     // Dados da idade da criança passado pelo backend
     document.addEventListener('DOMContentLoaded', function() {
        @foreach($radarDataCompetences as $competenceData)
            const ctx{{ $loop->index }} = document.getElementById('percentilChart-{{ $loop->index }}').getContext('2d');
            new Chart(ctx{{ $loop->index }}, {
                type: 'bar',
                data: {
                    labels: ['25%', '50%', '75%', '90%'], // Percentis no eixo X
                    datasets: [
                        {
                            label: 'Percentis',
                            data: [{{ $competenceData['percentil_25'] }}, {{ $competenceData['percentil_50'] }}, {{ $competenceData['percentil_75'] }}, {{ $competenceData['percentil_90'] }}],
                            backgroundColor: 'rgba(255, 159, 64, 0.6)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Idade da Criança',
                            data: [
                                {{ $ageInMonths < $competenceData['percentil_50'] ? $ageInMonths : 'null' }},
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
                                callback: function(value) {
                                    return value + ' meses'; // Exibe a idade em meses no eixo Y
                                },
                                stepSize: 1, // Define um passo de 5 meses para o eixo Y
                                max: Math.max({{ $competenceData['percentil_90'] }}, {{ $ageInMonths }}) + 10 // Ajusta o limite máximo do gráfico para garantir que a linha da idade caiba
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
</script>

@endpush