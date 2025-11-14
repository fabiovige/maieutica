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

    <!-- Info Card da Criança -->
    <div class="row mb-4">
        <div class="col-12">
            <x-kid-info-card :kid="$kid" />
        </div>
    </div>

    <!-- Filtros na Horizontal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">
                        <!-- Primeiro Checklist -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="firstChecklistId" class="form-label">Primeiro Checklist:</label>
                                <select name="firstChecklistId" id="firstChecklistId" class="form-select">
                                    <option value="">-- Selecione --</option>
                                    @foreach($allChecklists as $checklist)
                                    <option value="{{ $checklist->id }}" {{ $firstChecklist?->id === $checklist->id ? 'selected' : '' }}>
                                        {{ $checklist->name ?? 'Checklist ' . $checklist->id }} - {{ $checklist->created_at->format('d/m/Y') }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Segundo Checklist -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="secondChecklistId" class="form-label">Segundo Checklist:</label>
                                <select name="secondChecklistId" id="secondChecklistId" class="form-select">
                                    <option value="">-- Selecione --</option>
                                    @foreach($allChecklists as $checklist)
                                    <option value="{{ $checklist->id }}" {{ $secondChecklist?->id === $checklist->id ? 'selected' : '' }}>
                                        {{ $checklist->name ?? 'Checklist ' . $checklist->id }} - {{ $checklist->created_at->format('d/m/Y') }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Nível -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="comparisonLevelId" class="form-label">Nível:</label>
                                <select name="comparisonLevelId" id="comparisonLevelId" class="form-select">
                                    <option value="">-- Selecione --</option>
                                    <option value="0" {{ ($levelId==0) ? ' selected ' : '' }}>Todos os níveis</option>
                                    @foreach($levels as $level)
                                    <option value="{{ $level }}" {{ ($levelId==$level) ? ' selected ' : '' }}>
                                        Nível {{ $level }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Status dos Checklists -->
                        <div class="col-md-1">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#statusModal">
                                <i class="bi bi-info-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal com Status dos Checklists -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Status dos Checklists</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($firstChecklist)
                    <p><strong>Primeiro Checklist:</strong> {{ $firstChecklist->name ?? 'Checklist ' . $firstChecklist->id }} -
                        {{ $firstChecklist->created_at->format('d/m/Y') }}</p>
                    @endif

                    @if($secondChecklist)
                    <p><strong>Segundo Checklist:</strong> {{ $secondChecklist->name ?? 'Checklist ' . $secondChecklist->id }} -
                        {{ $secondChecklist->created_at->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="row">
        <!-- Gráficos -->
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <canvas id="barChart" width="400" height="200"></canvas>
                    <div class="mt-4">
                        @php
                            // Prepara labels e datasets
                            $radarLabels = array_column($radarDataDomains, 'domain');

                            // Datasets para o gráfico de radar (escala 0-3)
                            $radarDatasets = [];

                            // Datasets para o gráfico de barras (escala 0-100 percentual)
                            $barDatasets = [];

                            if ($firstChecklist) {
                                $firstData = array_map(fn($item) => $item['firstAverage'] ?? 0, $radarDataDomains);

                                // Para o radar: escala 0-3
                                $radarDatasets[] = [
                                    'label' => 'Checklist 1 - ' . $firstChecklist->created_at->format('d/m/Y'),
                                    'data' => $firstData,
                                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                                    'borderColor' => 'rgba(54, 162, 235, 1)',
                                    'borderWidth' => 1
                                ];

                                // Para o bar: converte para percentual (0-100)
                                $barDatasets[] = [
                                    'label' => 'Checklist 1 - ' . $firstChecklist->created_at->format('d/m/Y'),
                                    'data' => array_map(fn($value) => round(($value / 3) * 100, 1), $firstData),
                                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                                    'borderColor' => 'rgba(54, 162, 235, 1)',
                                    'borderWidth' => 1
                                ];
                            }

                            if ($secondChecklist) {
                                $secondData = array_map(fn($item) => $item['secondAverage'] ?? 0, $radarDataDomains);

                                // Para o radar: escala 0-3
                                $radarDatasets[] = [
                                    'label' => 'Checklist 2 - ' . $secondChecklist->created_at->format('d/m/Y'),
                                    'data' => $secondData,
                                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                                    'borderColor' => 'rgba(255, 99, 132, 1)',
                                    'borderWidth' => 1
                                ];

                                // Para o bar: converte para percentual (0-100)
                                $barDatasets[] = [
                                    'label' => 'Checklist 2 - ' . $secondChecklist->created_at->format('d/m/Y'),
                                    'data' => array_map(fn($value) => round(($value / 3) * 100, 1), $secondData),
                                    'backgroundColor' => 'rgba(255, 99, 132, 0.6)',
                                    'borderColor' => 'rgba(255, 99, 132, 1)',
                                    'borderWidth' => 1
                                ];
                            }
                        @endphp

                        <x-radar-chart
                            :labels="$radarLabels"
                            :datasets="$radarDatasets"
                            canvasId="radarChart"
                            :showPercentageInTooltip="true"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Domínios -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-grid-3x3-gap-fill me-2"></i>Domínios
                    </h5>
                </div>
                <div class="card-body p-0">
                    @foreach($domains as $domain)
                    <a href="{{ route('kids.domainDetails', [
                        'kidId' => $kid->id,
                        'levelId' => $levelId,
                        'domainId' => $domain->id,
                        'checklistId' => $secondChecklist?->id ?? $firstChecklist?->id
                    ]) }}"
                    class="text-decoration-none">
                        <div class="d-flex align-items-center p-3 border-bottom position-relative domain-item">
                            <div class="flex-shrink-0 me-3">
                                <div class="domain-icon rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px; background-color: {{ $domain->color ?? '#6c757d' }};">
                                    <span class="text-white fw-bold">{{ $domain->initial }}</span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 text-dark">{{ $domain->name }}</h6>
                            </div>
                            <div class="position-absolute end-0 me-3">
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script type="text/javascript">
    // Registrar o plugin
    Chart.register(ChartDataLabels);

    document.addEventListener('DOMContentLoaded', function() {
        // Dados para o Gráfico de Barras
        var ctxBar = document.getElementById('barChart').getContext('2d');
        var radarLabels = @json($radarLabels);
        var barDatasets = @json($barDatasets);

        // Gráfico de Barras (em percentual)
        var barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: radarLabels,
                datasets: barDatasets
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: {
                            stepSize: 25,
                            callback: function (value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Percentual de Desenvolvimento'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Domínios'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toFixed(1) + '%';
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#000',
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        formatter: function(value) {
                            return value.toFixed(1) + '%';
                        },
                        padding: 4
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
    });
</script>
@endpush

@push('styles')
<style>
.domain-item {
    transition: all 0.3s ease;
}

.domain-item:hover {
    background-color: rgba(0,0,0,0.03);
}

.domain-item:last-child {
    border-bottom: none !important;
}

.domain-icon {
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.domain-item:hover .domain-icon {
    transform: scale(1.1);
}
</style>
@endpush
