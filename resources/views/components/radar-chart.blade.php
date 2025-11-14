@props([
    'labels' => [],
    'datasets' => [],
    'canvasId' => 'radarChart',
    'showPercentageInTooltip' => true,
    'usePercentageScale' => false,
    'width' => '400',
    'height' => '400'
])

<div class="radar-chart-container">
    <canvas id="{{ $canvasId }}" width="{{ $width }}" height="{{ $height }}"></canvas>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
        <script src="{{ asset('js/radar-chart-helper.js') }}?v={{ filemtime(public_path('js/radar-chart-helper.js')) }}"></script>
    @endpush
@endonce

@push('scripts')
<script type="text/javascript">
    (function() {
        var radarLabels_{{ $canvasId }} = @json($labels);
        var radarDatasets_{{ $canvasId }} = @json($datasets);
        var showPercentage_{{ $canvasId }} = {{ $showPercentageInTooltip ? 'true' : 'false' }};
        var usePercentageScale_{{ $canvasId }} = {{ $usePercentageScale ? 'true' : 'false' }};

        // Função para criar o gráfico
        function initRadarChart_{{ $canvasId }}() {
            if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined' && typeof createRadarChart !== 'undefined') {
                // Registra o plugin datalabels se ainda não estiver registrado
                if (!Chart.registry.plugins.get('datalabels')) {
                    Chart.register(ChartDataLabels);
                }
                createRadarChart('{{ $canvasId }}', radarLabels_{{ $canvasId }}, radarDatasets_{{ $canvasId }}, showPercentage_{{ $canvasId }}, usePercentageScale_{{ $canvasId }});
            }
        }

        // Tenta criar imediatamente se já carregado
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRadarChart_{{ $canvasId }});
        } else {
            // DOM já carregado, executa imediatamente
            initRadarChart_{{ $canvasId }}();
        }
    })();
</script>
@endpush
