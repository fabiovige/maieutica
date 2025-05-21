@extends('layouts.app')

@section('title')
Comparativo
@endsection

@section('breadcrumb-items')
<li class="breadcrumb-item">
    <a href="{{ route('kids.index') }}">
        <i class="bi bi-people"></i> Crianças
    </a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    <i class="bi bi-clipboard-data"></i> Comparativo
</li>
@endsection

@section('content')

<div class="row" id="app">
    <div class="col-md-12 mb-4">
        <x-kid-info-card :kid="$kid" />
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Filtros -->
            <h4>Filtros</h4>

            <div class="form-group mb-3">
                <label for="firstChecklistId">Primeiro Checklist:</label>
                <select name="firstChecklistId" id="firstChecklistId" class="form-control">
                    <option value="">-- Selecione --</option>
                    @foreach($allChecklists as $checklist)
                    <option value="{{ $checklist->id }}" {{ isset($firstChecklist) && $firstChecklist->id === $checklist->id ? 'selected' : '' }}>
                        {{ $checklist->name ?? 'Checklist ' . $checklist->id }} - {{ $checklist->created_at->format('d/m/Y') }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="secondChecklistId">Segundo Checklist:</label>
                <select name="secondChecklistId" id="secondChecklistId" class="form-control">
                    <option value="">-- Selecione --</option>
                    @foreach($allChecklists as $checklist)
                    <option value="{{ $checklist->id }}" {{ isset($secondChecklist) && $secondChecklist->id === $checklist->id ? 'selected' : '' }}>
                        {{ $checklist->name ?? 'Checklist ' . $checklist->id }} - {{ $checklist->created_at->format('d/m/Y') }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Combobox para seleção de levels de comparação -->
            @if($levels)
            <div class="form-group mb-3">
                <label for="comparisonLevelId">Selecionar um nível para comparação:</label>
                <select name="comparisonLevelId" id="comparisonLevelId" class="form-control">
                    <option value="">-- Selecione --</option>
                    <option value="0" {{ ($levelId==0) ? ' selected ' : '' }}>Todos os níveis</option>
                    @foreach($levels as $level)
                    <option value="{{ $level }}" {{ ($levelId==$level) ? ' selected ' : '' }}>
                        Nível - {{ $level }}
                    </option>
                    @endforeach
                </select>
            </div>
            @else
            <p>Não há níveis disponíveis para comparação.</p>
            @endif

            <!-- Status dos Checklists -->
            @if($firstChecklist)
            <p><strong>Primeiro Checklist:</strong> {{ $firstChecklist->name ?? 'Checklist ' . $firstChecklist->id }} -
                {{ $firstChecklist->created_at->format('d/m/Y') }}</p>
            @endif

            @if($secondChecklist)
            <p><strong>Segundo Checklist:</strong> {{ $secondChecklist->name ?? 'Checklist ' . $secondChecklist->id }} -
                {{ $secondChecklist->created_at->format('d/m/Y') }}</p>
            @endif

            <!-- Domínios -->
            <h4 class="mt-3">Domínios</h4>
            <ul>
                @foreach($domains as $domain)
                <li>
                    <a href="{{ route('kids.domainDetails', ['kidId' => $kid->id, 'levelId' => $levelId, 'domainId' => $domain->id, 'checklistId' => $secondChecklist->id ?? $firstChecklist->id]) }}">
                        {{ $domain->name }} ({{ $domain->initial }})
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="col-md-8">
            <canvas id="barChart" width="400" height="200"></canvas>
            <div class="mt-4">
                <canvas id="radarChart" width="400" height="400"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    // Dados para os Gráficos
    var ctxRadar = document.getElementById('radarChart').getContext('2d');
    var ctxBar = document.getElementById('barChart').getContext('2d');

    var radarLabels = @json(array_column($radarDataDomains, 'domain'));

    var radarDataFirst = @json(array_map(function ($item) {
        return $item['firstAverage'] ?? 0;
    }, $radarDataDomains));

    var radarDataSecond = @json(array_map(function ($item) {
        return $item['secondAverage'] ?? 0;
    }, $radarDataDomains));

    var datasets = [];

    @if ($firstChecklist)
        datasets.push({
            label: 'Checklist 1 - {{ $firstChecklist->created_at->format('d/m/Y') }}',
            data: radarDataFirst,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        });
    @endif

    @if ($secondChecklist)
        datasets.push({
            label: 'Checklist 2 - {{ $secondChecklist->created_at->format('d/m/Y') }}',
            data: radarDataSecond,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        });
    @endif

    // Configuração comum para os gráficos
    var commonOptions = {
        scales: {
            r: {
                suggestedMin: 0,
                suggestedMax: 3,
                ticks: {
                    stepSize: 1,
                    callback: function (value) {
                        if (value === 0) return 'Não observado';
                        if (value === 1) return 'Não desenvolvido';
                        if (value === 2) return 'Em desenvolvimento';
                        if (value === 3) return 'Desenvolvido';
                        return value;
                    }
                }
            }
        }
    };

    // Gráfico de Radar
    var radarChart = new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: radarLabels,
            datasets: datasets
        },
        options: commonOptions
    });

    // Gráfico de Barras
    var barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: radarLabels,
            datasets: datasets
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMin: 0,
                    suggestedMax: 3,
                    ticks: {
                        stepSize: 1,
                        callback: function (value) {
                            if (value === 0) return 'Não observado';
                            if (value === 1) return 'Não desenvolvido';
                            if (value === 2) return 'Em desenvolvimento';
                            if (value === 3) return 'Desenvolvido';
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

    // Função para atualizar a URL quando os selects mudarem
    function updateUrl() {
        var firstChecklistId = document.getElementById('firstChecklistId').value;
        var secondChecklistId = document.getElementById('secondChecklistId').value;
        var levelId = document.getElementById('comparisonLevelId').value;

        if (firstChecklistId && secondChecklistId && levelId) {
            var url = "{{ url('analysis') }}/" + "{{ $kid->id }}" +
                     "/level/" + levelId + "/" +
                     firstChecklistId + "/" + secondChecklistId;
            window.location.href = url;
        }
    }

    // Adicionar listeners para os selects
    document.getElementById('firstChecklistId').addEventListener('change', updateUrl);
    document.getElementById('secondChecklistId').addEventListener('change', updateUrl);
    document.getElementById('comparisonLevelId').addEventListener('change', updateUrl);
</script>
@endpush
