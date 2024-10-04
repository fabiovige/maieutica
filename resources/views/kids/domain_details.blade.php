<!-- resources/views/kids/domain_details.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <h1>Detalhes do Domínio: {{ $domain->name }} ({{ $domain->initial }}) - Nível {{ $levelId }}</h1>
            <p><strong>Criança:</strong> {{ $kid->name }}</p>
            <p><strong>Idade:</strong> {{ $ageInMonths }} meses</p>

            @if($currentChecklist)
                <p><strong>Checklist Atual:</strong> {{ $currentChecklist->name ?? 'Checklist ' . $currentChecklist->id }} - {{ $currentChecklist->created_at->format('d/m/Y') }}</p>
            @else
                <p><strong>Checklist Atual:</strong> Não disponível</p>
            @endif

            @if($previousChecklist)
                <p><strong>Checklist Anterior:</strong> {{ $previousChecklist->name ?? 'Checklist ' . $previousChecklist->id }} - {{ $previousChecklist->created_at->format('d/m/Y') }}</p>
            @else
                <p><strong>Checklist Anterior:</strong> Não disponível</p>
            @endif
        </div>
    </div>


    <div class="row">
        <div class="col-md-7">
            <!-- Lista de Competências e Status -->
            <h2>Competências</h2>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Competência</th>
                        <th>Status Atual</th>
                        <th>Status Anterior</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($radarDataCompetences as $competenceData)
                    <tr>
                        <td>{{ $competenceData['competence'] }}</td>
                        <td>{{ $competenceData['description'] ?? '' }}</td>
                        <td>
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
                        <td>
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-md-5">
            <!-- Gráfico de Radar Detalhado por Competências -->
            <canvas id="radarChartCompetences" width="400" height="400"></canvas>
        </div>
    </div>






    <!-- Botão para voltar -->
    <a href="{{ route('kids.radarChart2', ['kidId' => $kid->id, 'levelId' => $levelId]) }}" class="btn btn-secondary">Voltar</a>
</div>

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
            label: 'Checklist Anterior - {{ $previousChecklist->created_at->format("d/m/Y") }}',
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
</script>
@endsection
