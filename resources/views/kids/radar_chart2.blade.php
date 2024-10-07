@extends('layouts.app')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
        <li class="breadcrumb-item active" aria-current="page">Análise Geral</li>
    </ol>
</nav>
@endsection

@section('content')

<div class="row" id="app">
    <div class="row">
        <div class="col-md-4">
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
        <div class="col-md-8">
            <canvas id="barChart" width="400" height="200"></canvas>
        </div>
    </div>
    <div class="d-flex justify-content-between">
        <div>



            <!-- filtros e dominios-->
            <h4>Filtros</h4>
            <div class="form-group">
                <label for="comparisonChecklistId">Selecionar um checklist para comparação:</label>
                <select name="comparisonChecklistId" id="comparisonChecklistId" class="form-control">
                    <option value="{{ $currentChecklist->id }}">Checklist {{ $currentChecklist->id }} Atual - {{ $currentChecklist->created_at->format('d/m/Y') }}</option>
                    @if($allChecklists->count() > 0)
                    @foreach($allChecklists as $checklist)
                    <option value="{{ $checklist->id }}"
                        {{ isset($previousChecklist) && $previousChecklist->id === $checklist->id ? 'selected' : '' }}>
                        {{ $checklist->name ?? 'Checklist ' . $checklist->id }} - {{ $checklist->created_at->format('d/m/Y') }}
                    </option>
                    @endforeach
                    @endif
                </select>
            </div>

            <!-- Combobox para seleção de levels de comparação -->
            @if($levels)
            <div class="form-group">
                <label for="comparisonLevelId">Selecionar um nível para comparação:</label>
                <select name="comparisonLevelId" id="comparisonLevelId" class="form-control">
                    <option value="">-- Selecione --</option>
                    @foreach($levels as $level)
                    <option value="{{ $level }}"
                        {{ ($levelId == $level) ? ' selected ' : '' }}>
                        Nível - {{ $level }}
                    </option>
                    @endforeach
                </select>
            </div>
            @else
            <p>Não há nível disponíveis para comparação.</p>
            @endif
            <!-- end filtros-->

            <!-- dominios-->
            <h4 class="mt-3">Domínios</h4>
            <ul>
                @foreach($domains as $domain)
                <li>
                    <a href="{{ route('kids.domainDetails', ['kidId' => $kid->id, 'levelId' => $levelId, 'domainId' => $domain->id, 'checklistId' => $previousChecklist->id ?? $currentChecklist->id]) }}">
                        {{ $domain->name }} ({{ $domain->initial }})
                    </a>
                </li>
                @endforeach
            </ul>
            <!-- fim dominios-->

        </div>
        <div class="col-md-7">
            <canvas id="radarChart" width="400" height="400"></canvas>
        </div>
    </div>
</div>


@endsection

@push('scripts')

<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    // Dados para o Gráfico de Radar
    var ctxRadar = document.getElementById('radarChart').getContext('2d');
    var ctxBar = document.getElementById('barChart').getContext('2d');

    var radarLabels = @json(array_column($radarDataDomains, 'domain'));

    var radarDataCurrent = @json(array_map(function($item) {
        return $item['currentAverage'] ?? 0;
    }, $radarDataDomains));

    var radarDataPrevious = @json(array_map(function($item) {
        return $item['previousAverage'] ?? 0;
    }, $radarDataDomains));

    var radarDataComparison = @json(array_map(function($item) {
        return $item['comparisonAverage'] ?? 0;
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
        label: 'Checklist de Comparação',
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

    var barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: radarLabels, // Os mesmos labels do gráfico de radar
            datasets: datasets // Os mesmos datasets usados no gráfico de radar
        },
        options: {
            scales: {
                y: { // Ajustamos o eixo Y para refletir os valores 1, 2 e 3
                    beginAtZero: true,
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
                    },
                    title: {
                        display: true,
                        text: 'Nível'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Competências'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });



    // Evento para redirecionar ao selecionar o checklist no combobox
    document.getElementById('comparisonChecklistId').addEventListener('change', function() {
        var comparisonChecklistId = this.value;
        if (comparisonChecklistId) {
            var url = "{{ url('analysis/' . $kid->id . '/level/' . $levelId) }}/" + comparisonChecklistId;
            window.location.href = url;
        }
    });


    // Evento para redirecionar ao selecionar o checklist no combobox
    document.getElementById('comparisonLevelId').addEventListener('change', function() {
        console.log(document.getElementById('comparisonChecklistId'));
        var comparisonLevelId = this.value;
        var comparisonChecklistId = document.getElementById('comparisonChecklistId').value;
        // Se nenhum checklist for selecionado, use o checklist atual
        if (!comparisonChecklistId) {
            comparisonChecklistId = "{{ $currentChecklist->id }}"; // Usar o checklist atual se nenhum for selecionado
        }

        if (comparisonChecklistId && comparisonLevelId) {
            // Usando os valores dinâmicos de kidId, levelId, comparisonChecklistId e comparisonLevelId na URL
            var url = "{{ url('analysis') }}/" + "{{ $kid->id }}" + "/level/" + comparisonLevelId + "/" + comparisonChecklistId;
            window.location.href = url;
        }
    });
</script>

@endpush