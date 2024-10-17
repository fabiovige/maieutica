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
        <div class="col-md-8">
            <!-- Tabela de Detalhes por Domínio -->
            <h3>Domínios</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Domínio</th>
                        <th>Total Itens</th>
                        <th>Itens Testados</th>
                        <th>Itens Válidos</th>
                        <th>Itens Inválidos</th>
                        <th>Percentual(%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($domainData as $domain)
                    <tr>
                        <td>{{ $domain['name'] }}</td>

                        <td>{{ $domain['itemsTotal'] }}</td>
                        <td>{{ $domain['itemsTested'] }}</td>
                        <td>{{ $domain['itemsValid'] }}</td>
                        <td>{{ $domain['itemsInvalid'] }}</td>
                        <td>{{ $domain['percentage'] }}%</td>
                    </tr>
                    @endforeach
                    <tr>
                        <th>Total</th>
                        <th>{{ $totalItemsTotal }}</th>
                        <th>{{ $totalItemsTested }}</th>
                        <th>{{ $totalItemsValid }}</th>
                        <th>{{ round($totalPercentage, 2) }}%</th>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
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
            <canvas id="barChartItems2" width="400" height="200"></canvas>
        </div>
    </div>-->

</div>

@endsection

@push('scripts')

<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    var ctxBar = document.getElementById('barChart').getContext('2d');
    var ctxRadar = document.getElementById('radarChart').getContext('2d');
    //var ctxRadarItems = document.getElementById('radarChartItems').getContext('2d');    
    //var ctxBarItems = document.getElementById('barChartItems').getContext('2d');
    var ctxBarItems2 = document.getElementById('barChartItems2').getContext('2d');

    var domainLabels = @json(array_column($domainData, 'name'));
    var domainPercentages = @json(array_column($domainData, 'percentage'));

    var domainItemsTested = @json(array_column($domainData, 'itemsTested'));
    var domainItemsValid = @json(array_column($domainData, 'itemsValid'));
    var domainItemsInvalid = @json(array_column($domainData, 'itemsInvalid'));
    var domainItemsTotal = @json(array_column($domainData, 'itemsTotal'));

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

// Configurações para a grade
const DISPLAY = true;
const BORDER = true;
const CHART_AREA = true;
const TICKS = true;

// Configuração dos dados
const data = {
  labels: domainLabels,
  datasets: [
    {
      label: 'Total Itens',
      data: domainItemsTotal,
      backgroundColor: 'rgba(255, 159, 64, 0.6)', // Laranja
      borderColor: 'rgba(255, 159, 64, 1)',
      borderWidth: 1
    },
    {
      label: 'Itens Testados',
      data: domainItemsTested,
      backgroundColor: 'rgba(54, 162, 235, 0.6)', // Azul
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1
    },
    {
      label: 'Itens Válidos',
      data: domainItemsValid,
      backgroundColor: 'rgba(75, 192, 192, 0.6)', // Verde
      borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 1
    },
    {
      label: 'Itens Inválidos',
      data: domainItemsInvalid,
      //backgroundColor: 'rgba(75, 192, 192, 0.6)', // Verde
      //borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 1
    },
    {
      label: 'Percentual (%)',
      data: domainPercentages,
      backgroundColor: 'rgba(153, 102, 255, 0.6)', // Roxo
      borderColor: 'rgba(153, 102, 255, 1)',
      borderWidth: 1,
      type: 'line', // Exibir como linha
      yAxisID: 'y1',
      fill: false
    }
  ]
};

// Configuração do gráfico
const config = {
  type: 'bar',
  data: data,
  options: {
    responsive: true,
    indexAxis: 'x', // 'x' para barras verticais
    plugins: {
      title: {
        display: true,
        text: 'Análise de Domínios'
      },
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
    },
    scales: {
      x: {
        border: {
          display: BORDER
        },
        grid: {
          display: DISPLAY,
          drawOnChartArea: CHART_AREA,
          drawTicks: TICKS,
        },
        title: {
          display: true,
          text: 'Domínios'
        }
      },
      y: {
        beginAtZero: true,
        border: {
          display: BORDER
        },
        grid: {
          display: DISPLAY,
          drawOnChartArea: CHART_AREA,
          drawTicks: TICKS,
          color: function(context) {
            if (context.tick.value > 0) {
              return 'rgba(0, 255, 0, 0.3)'; // Verde para valores positivos
            } else if (context.tick.value < 0) {
              return 'rgba(255, 0, 0, 0.3)'; // Vermelho para valores negativos
            }
            return '#000000'; // Preto para zero
          },
        },
        ticks: {
          stepSize: 25,
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
        border: {
          display: BORDER
        },
        grid: {
          display: DISPLAY,
          drawOnChartArea: false, // Não desenhar linhas de grade para o eixo secundário
          drawTicks: TICKS
        },
        ticks: {
          callback: function(value) {
            return value + '%';
          },
          stepSize: 10,
          max: 100
        },
        title: {
          display: true,
          text: 'Percentual (%)'
        }
      }
    }
  }
};

// Criação do gráfico
const barChartItems2 = new Chart(
  document.getElementById('barChartItems2'),
  config
);

// Ações (opcional)
const actions = [
  {
    name: 'Atualizar Dados',
    handler(chart) {
      // Implemente a lógica para atualizar os dados aqui
      chart.update();
    }
  }
];


   

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