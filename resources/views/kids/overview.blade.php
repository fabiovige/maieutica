@extends('layouts.app')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
        <li class="breadcrumb-item active" aria-current="page">Visão Geral</li>
    </ol>
</nav>
@endsection

@section('content')

<div class="row" id="app">
    <div class="row">
        <div class="col-md-6">

            <Resume :responsible="{{ $kid->responsible()->first() }}"
                :professional="{{ $kid->professional()->first() }}" :kid="{{ $kid }}"
                :checklist="{{ $kid->checklists()->count() }}" :plane="{{ $kid->planes()->count() }}"
                :months="{{ $ageInMonths }}">
            </Resume>



        </div>
        <div class="col-md-6">

            <h3>Checklists</h3>
            @if($currentChecklist)
            <p><strong>Checklist Atual:</strong> {{ $currentChecklist->name ?? 'Checklist ' . $currentChecklist->id }} -
                {{ $currentChecklist->created_at->format('d/m/Y') }}</p>
            @else
            <p><strong>Checklist Atual:</strong> Não disponível</p>
            @endif

            <h3>Idade de Desenvolvimento</h3>
            <p><strong>Idade Cronológica:</strong> {{ $ageInMonths }} meses</p>
            <p><strong>Idade de Desenvolvimento:</strong> {{ round($developmentalAgeInMonths, 0) }} meses</p>
            <p><strong>Atraso:</strong> {{ round($delayInMonths, 0) }} meses</p>


            <div class="form-group">
                <label for="levelSelect">Selecionar Nível:</label>
                <select id="levelSelect" class="form-control" onchange="changeLevel(this.value)">
                    <option value="" {{ is_null($levelId) ? 'selected' : '' }}>Todos os Níveis</option>
                    @foreach($levels as $level)
                    <option value="{{ $level }}" {{ ($levelId==$level) ? 'selected' : '' }}>Nível {{ $level }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 d-flex justify-content-center">
            <h2>{{ $levelId ? 'Nível ' . $levelId: 'Todos os Níveis'}}</h2>
        </div>
    </div>

    <div class="row mt-3 d-flex justify-content-center">
        <div class="col-md-12">
            <h3>Avaliação</h3>
            <canvas id="barChart" width="400" height="200"></canvas>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <h3>Gráfico de radar da avaliação</h3>
            <canvas id="radarChart" width="400" height="200"></canvas>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h2>Análise geral dos itens</h2>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-7">
            <!-- Tabela de Detalhes por Domínio -->
            <h3>Domínios</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Domínio</th>
                        <th>Itens Testados</th>
                        <th>Itens Válidos</th>
                        <th>Percentual(%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($domainData as $domain)
                    <tr>
                        <td>{{ $domain['name'] }}</td>
                        <td>{{ $domain['itemsTested'] }}</td>
                        <td>{{ $domain['itemsValid'] }}</td>
                        <td>{{ $domain['percentage'] }}%</td>
                    </tr>
                    @endforeach
                    <tr>
                        <th>Total</th>
                        <th>{{ $totalItemsTested }}</th>
                        <th>{{ $totalItemsValid }}</th>
                        <th>{{ round($totalPercentage, 2) }}%</th>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-5">
            <!-- Áreas Frágeis -->
            <h3>Áreas Frágeis</h3>
            @if(count($weakAreas) > 0)
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Domínio</th>
                        <th>Percentual (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weakAreas as $area)
                    <tr>
                        <td>{{ $area['name'] }}</td>
                        <td>{{ $area['percentage'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>Nenhuma área frágil identificada.</p>
            @endif

        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <h2>Representação gráfica da análise geral</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <!-- Novo Gráfico de Barras para Itens Testados e Válidos por Domínio -->
            <canvas id="barChartItems" width="400" height="200"></canvas>
        </div>
    </div>

</div>

@endsection

@push('scripts')

<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    var ctxBar = document.getElementById('barChart').getContext('2d');
    var ctxRadar = document.getElementById('radarChart').getContext('2d');
    //var ctxRadarItems = document.getElementById('radarChartItems').getContext('2d');    
    var ctxBarItems = document.getElementById('barChartItems').getContext('2d');

    var domainLabels = @json(array_column($domainData, 'name'));
    var domainPercentages = @json(array_column($domainData, 'percentage'));

    var domainItemsTested = @json(array_column($domainData, 'itemsTested'));
    var domainItemsValid = @json(array_column($domainData, 'itemsValid'));

    var barColors = domainPercentages.map(function(percentage) {
        return percentage < 70 ? 'rgba(255, 99, 132, 0.6)' : 'rgba(75, 192, 192, 0.6)';
    });

    var fullPercentages = domainLabels.map(function() {
        return 100;
    });

    var barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: domainLabels,
            datasets: [{
                label: 'Percentual de Habilidades Adquiridas',
                data: domainPercentages,
                backgroundColor: barColors,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // Barras horizontais
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + "%";
                        }
                    },
                    title: {
                        display: true,
                        text: 'Percentual (%)'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Domínios'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.x + '%';
                        }
                    }
                }
            }
        },
        plugins: [{
            afterDatasetsDraw: function(chart) {
                var ctx = chart.ctx;

                chart.data.datasets.forEach(function(dataset, i) {
                    var meta = chart.getDatasetMeta(i);
                    meta.data.forEach(function(bar, index) {
                        var data = dataset.data[index];
                        ctx.save();
                        ctx.fillStyle = 'black'; // Cor do texto
                        ctx.font = '12px sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        var xPos = bar.tooltipPosition().x - 30;
                        var yPos = bar.tooltipPosition().y;

                        ctx.fillText(data + '%', xPos, yPos);
                        ctx.restore();
                    });
                });
            }
        }]        
    });

    // Radar Chart
    var fullPercentages = domainLabels.map(function() {
        return 100;
    });

    var radarChart = new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: domainLabels,
            datasets: [
                {
                    label: 'Percentual de Habilidades Adquiridas',
                    data: domainPercentages,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)', // Blue fill
                    borderColor: 'rgba(54, 162, 235, 1)', // Blue line
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                },
                {
                    label: 'Percentual Esperado (100%)',
                    data: fullPercentages,
                    backgroundColor: 'rgba(0, 0, 0, 0)', // Transparent fill
                    borderColor: 'rgba(255, 99, 132, 1)', // Red line
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    tension: 0.4 // Suaviza a linha
                }
            ]
        },
        options: {
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 25,
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.r + '%';
                        }
                    }
                }
            }
        }
    });

    // radarChartItems
    /*var radarChartItems = new Chart(ctxRadarItems, {
        type: 'radar',
        data: {
            labels: domainLabels,
            datasets: [
                {
                    label: 'Itens Testados',
                    data: domainItemsTested,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)', // Preenchimento laranja
                    borderColor: 'rgba(255, 159, 64, 1)', // Linha laranja
                    pointBackgroundColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 2
                },
                {
                    label: 'Itens Válidos',
                    data: domainItemsValid,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Preenchimento verde-água
                    borderColor: 'rgba(75, 192, 192, 1)', // Linha verde-água
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.4 // Suaviza a linha
                }
            ]
        },
        options: {
            scales: {
                r: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 25,
                        precision: 0,
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return value;
                            }
                        }
                    },
                    grid: {
                        color: 'rgba(200, 200, 200, 0.2)'
                    },
                    angleLines: {
                        color: 'rgba(200, 200, 200, 0.2)'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.r;
                        }
                    }
                }
            }
        }
    });
    */

    // barChartItems
// barChartItems
var barChartItems = new Chart(ctxBarItems, {
    type: 'bar',
    data: {
        labels: domainLabels,
        datasets: [
            {
                label: 'Itens Testados',
                data: domainItemsTested,
                backgroundColor: 'rgba(255, 159, 64, 0.6)', // Preenchimento laranja
                borderColor: 'rgba(255, 159, 64, 1)',        // Borda laranja
                borderWidth: 1
            },
            {
                label: 'Itens Válidos',
                data: domainItemsValid,
                backgroundColor: 'rgba(75, 192, 192, 0.6)', // Preenchimento verde-água
                borderColor: 'rgba(75, 192, 192, 1)',        // Borda verde-água
                borderWidth: 1
            },
            {
                    label: 'Percentual (%)',
                    data: domainPercentages,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)', // Roxo
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1,
                    type: 'bar',
                    yAxisID: 'y1'
                }
        ]
    },
    options: {
        indexAxis: 'x', // 'x' para barras verticais, 'y' para barras horizontais
        scales: {
            x: {
                stacked: false, // Altere para 'true' para barras empilhadas
                title: {
                    display: true,
                    text: 'Domínios'
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 5,
                    precision: 0
                },
                title: {
                    display: true,
                    text: 'Número de Itens'
                }
            },
            y1: {
                    beginAtZero: true,
                    position: 'right',
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        },
                        stepSize: 10,
                        max: 100
                    },
                    grid: {
                        drawOnChartArea: false // Remove as linhas de grade do eixo secundário
                    },
                    title: {
                        display: true,
                        text: 'Percentual (%)'
                    }
                }
        },
        plugins: {
            legend: {
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y;
                    }
                }
            }
        }
    },
    plugins: [{
        afterDatasetsDraw: function(chart) {
            var ctx = chart.ctx;

            chart.data.datasets.forEach(function(dataset, i) {
                var meta = chart.getDatasetMeta(i);
                meta.data.forEach(function(bar, index) {
                    var data = dataset.data[index];
                    ctx.save();

                    // Definir cor do texto com base na cor da barra para melhor contraste
                    ctx.fillStyle = 'black'; // Você pode ajustar a cor conforme necessário
                    ctx.font = '12px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';

                    var position = bar.tooltipPosition();

                    if (chart.options.indexAxis === 'y') {
                        // Para barras horizontais
                        var xPos = position.x;
                        var yPos = position.y;
                    } else {
                        // Para barras verticais
                        var xPos = position.x;
                        var yPos = position.y - 5; // Ajuste a posição vertical conforme necessário
                    }

                    // Desenhar o valor da barra
                    ctx.fillText(data, xPos, yPos);

                    // Se for o dataset de 'Itens Válidos', desenhar o percentual
                    if (dataset.label === 'Itens Válidos') {
                        var percentage = domainPercentages[index];
                        // Ajustar a posição para não sobrepor o valor
                        var percentageYPos = yPos - 15; // Ajuste conforme necessário
                        //ctx.fillText(percentage + '%', xPos, percentageYPos);
                    }

                    ctx.restore();
                });
            });
        }
    }]
});


    // Evento para redirecionar ao selecionar o checklist no combobox
    document.getElementById('comparisonChecklistId').addEventListener('change', function() {
        var comparisonChecklistId = this.value;
        if (comparisonChecklistId) {
            var url = "{{ url('kids/overview/' . $kid->id . '/level/' . $levelId) }}/" + comparisonChecklistId;
            window.location.href = url;
        }
    });

    // Evento para redirecionar ao selecionar o nível no combobox
    document.getElementById('comparisonLevelId').addEventListener('change', function() {
        var comparisonLevelId = this.value;
        var comparisonChecklistId = document.getElementById('comparisonChecklistId').value || "{{ $currentChecklist->id }}";
        if (comparisonChecklistId && comparisonLevelId) {
            var url = "{{ url('kids/overview') }}/" + "{{ $kid->id }}" + "/level/" + comparisonLevelId + "/" + comparisonChecklistId;
            window.location.href = url;
        }
    });

    function changeLevel(selectedLevel) {
        var kidId = {{ $kid->id }};
        var baseUrl = "{{ url('kids') }}";
        var url = '';

        if (selectedLevel) {
            // Se um nível específico for selecionado
            url = baseUrl + "/" + kidId + "/level/" + selectedLevel + "/overview";
        } else {
            // Se "Todos os Níveis" for selecionado
            url = baseUrl + "/" + kidId + "/overview";
        }

        // Redirecionar para a URL construída
        window.location.href = url;
    }
</script>

@endpush