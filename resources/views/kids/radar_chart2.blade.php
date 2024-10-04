@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <h1>Avaliação Geral - Nível {{ $levelId }}</h1>
            <p><strong>Criança:</strong> {{ $kid->name }}</p>
            <p><strong>Idade:</strong> {{ $ageInMonths }} meses</p>

            <!-- Informações sobre os checklists -->
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
        <div class="col-md-4">
            <h2>Competências</h2>
            <ul>
                @foreach($domains as $domain)
                    <li>
                        <a href="{{ route('kids.domainDetails', ['kidId' => $kid->id, 'levelId' => $levelId, 'domainId' => $domain->id, 'checklistId' => $currentChecklist]) }}">
                            {{ $domain->name }} ({{ $domain->initial }})
                        </a>

                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-8">
                <!-- Gráfico de Radar Geral por Domínios -->
                <canvas id="radarChart" width="400" height="400"></canvas>
        </div>
    </div>


    <!-- Lista de Domínios -->



</div>

<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Dados para o Gráfico de Radar
    var ctxRadar = document.getElementById('radarChart').getContext('2d');
    var radarLabels = @json(array_column($radarDataDomains, 'domain'));

    var radarDataCurrent = @json(array_map(function($item) {
        return $item['currentAverage'] ?? 0;
    }, $radarDataDomains));

    var radarDataPrevious = @json(array_map(function($item) {
        return $item['previousAverage'] ?? 0;
    }, $radarDataDomains));

    var datasets = [];

    @if($currentChecklist)
        datasets.push({
            label: 'Checklist Atual',
            data: radarDataCurrent,
            backgroundColor: 'rgba(54, 162, 235, 0.2)', // Cor do preenchimento
            borderColor: 'rgba(54, 162, 235, 1)', // Cor da linha
            borderWidth: 1
        });
    @endif

    @if($previousChecklist)
        datasets.push({
            label: 'Checklist Anterior',
            data: radarDataPrevious,
            backgroundColor: 'rgba(255, 99, 132, 0.2)', // Cor do preenchimento
            borderColor: 'rgba(255, 99, 132, 1)', // Cor da linha
            borderWidth: 1
        });
    @endif

    var radarChart = new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: radarLabels,
            datasets: datasets
        },
        options: {
            scales: {
                r: {
                    suggestedMin: 1,
                    suggestedMax: 3,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
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
