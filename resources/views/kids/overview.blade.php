@extends('layouts.app')


@section('title')
    Desenvolvimento
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('kids.index') }}">
            <i class="bi bi-people"></i> Crianças
        </a>
    </li>

    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-people"></i> Desenvolvimento
    </li>
@endsection



@section('content')

    <div class="row">
        <div class="col-md-12 mb-4">
            <x-kid-info-card :kid="$kid" />
        </div>
    </div>



    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h3>Informações da Criança</h3>
                    @if ($currentChecklist)
                        <p><strong>Checklist Atual:</strong>
                            {{ $currentChecklist->name ?? 'Checklist ' . $currentChecklist->id }} -
                            {{ $currentChecklist->created_at->format('d/m/Y') }}</p>
                    @else
                        <p><strong>Checklist Atual:</strong> Não disponível</p>
                    @endif

                    <p><strong>Idade de Desenvolvimento:</strong> {{ round($developmentalAgeInMonths, 0) }} meses</p>
                    <p><strong>Atraso:</strong> {{ round($delayInMonths, 0) }} meses</p>

                    <p><button id="generatePdfBtn" class="btn btn-primary mt-3"><i class="bi bi-filetype-pdf"></i> Gerar
                            PDF</button></p>

                    <form id="pdfForm"
                        action="{{ route('kids.generatePdf', ['kidId' => $kid->id, 'levelId' => $levelId]) }}"
                        method="POST" style="display: none;" target="_blank">
                        @csrf
                        <input type="hidden" name="barChartImage" id="barChartImageInput">
                        <input type="hidden" name="radarChartImage" id="radarChartImageInput">
                        <input type="hidden" name="barChartItems2Image" id="barChartItems2ImageInput">
                    </form>
                </div>

                <div class="col-md-6">
                    <h3>Progresso Geral</h3>
                    <div class="progress" role="progressbar" aria-label="{{ $kid->name }}"
                        aria-valuenow="{{ $averagePercentage }}" aria-valuemin="0" aria-valuemax="100" style="height: 30px">
                        <div class="progress-bar progress-bar-striped" style="width: {{ $averagePercentage }}%">
                            {{ $averagePercentage }}%</div>
                    </div>

                    <div class="col-md-12 d-flex justify-content-start mt-3">
                        <h3>{{ $levelId ? 'Nível ' . $levelId : 'Todos os Níveis' }}</h3>
                    </div>

                    <label for="levelSelect">Selecionar Nível:</label>
                    <select id="levelSelect" class="form-control" onchange="changeLevel(this.value)">
                        <option value="" {{ is_null($levelId) ? 'selected' : '' }}>Todos os Níveis</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level }}" {{ $levelId == $level ? 'selected' : '' }}>Nível
                                {{ $level }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12 col-md-12 col-lg-12 my-3">
            <h3>Análise geral dos itens</h3>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs" id="chartTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="bar-tab" data-bs-toggle="tab" data-bs-target="#bar-chart"
                        type="button" role="tab" aria-controls="bar-chart" aria-selected="true">Gráfico de
                        Barras</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="radar-tab" data-bs-toggle="tab" data-bs-target="#radar-chart"
                        type="button" role="tab" aria-controls="radar-chart" aria-selected="false">Gráfico
                        Radar</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="domain-tab" data-bs-toggle="tab" data-bs-target="#domain-chart"
                        type="button" role="tab" aria-controls="domain-chart" aria-selected="false">Domínios e Áreas
                        Frágeis</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="geral-tab" data-bs-toggle="tab" data-bs-target="#geral-chart"
                        type="button" role="tab" aria-controls="geral-chart" aria-selected="false">Análise
                        Geral</button>
                </li>
            </ul>

            <div class="tab-content" id="chartTabsContent">
                <div class="tab-pane fade show active" id="bar-chart" role="tabpanel" aria-labelledby="bar-tab">
                    <div class="mt-3">
                        <canvas id="barChart" height="200"></canvas>
                    </div>
                </div>
                <div class="tab-pane fade" id="radar-chart" role="tabpanel" aria-labelledby="radar-tab">
                    <div class="mt-3">
                        <canvas id="radarChart" width="150" height="150"></canvas>
                    </div>
                </div>
                <div class="tab-pane fade" id="domain-chart" role="tabpanel" aria-labelledby="domain-tab">
                    <div class="mt-3">
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
                                        @foreach ($domainData as $domain)
                                            <tr>
                                                <td>{{ $domain['name'] }}</td>

                                                <td>{{ $domain['itemsTotal'] }}</td>
                                                <td>{{ $domain['itemsTested'] }}</td>
                                                <td>{{ $domain['itemsValid'] }}</td>
                                                <td>{{ $domain['itemsInvalid'] }}</td>
                                                <td>

                                                    <div class="progress" role="progressbar"
                                                        aria-label="{{ $domain['name'] }}"
                                                        aria-valuenow="{{ $domain['percentage'] }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                        <div class="progress-bar"
                                                            style="width: {{ $domain['percentage'] }}%">
                                                        </div>
                                                    </div>


                                                    {{ $domain['percentage'] }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <th>Total</th>
                                            <th>{{ $totalItemsTotal }}</th>
                                            <th>{{ $totalItemsTested }}</th>
                                            <th>{{ $totalItemsValid }}</th>
                                            <th>{{ round($totalPercentage, 2) }}%
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <!-- Áreas Frágeis -->
                                <h3>Áreas Frágeis</h3>
                                @if (count($weakAreas) > 0)
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Domínio</th>
                                                <th>Percentual (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($weakAreas as $area)
                                                <tr>
                                                    <td>{{ $area['name'] }}</td>
                                                    <td>
                                                        <div class="progress" role="progressbar"
                                                            aria-label="{{ $area['name'] }}"
                                                            aria-valuenow="{{ $area['percentage'] }}" aria-valuemin="0"
                                                            aria-valuemax="100">
                                                            <div class="progress-bar bg-warning"
                                                                style="width: {{ $area['percentage'] }}%"></div>
                                                        </div>
                                                        {{ $area['percentage'] }}%
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>Nenhuma área frágil identificada.</p>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="geral-chart" role="tabpanel" aria-labelledby="geral-tab">
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-12 col-md-12 col-lg-12 mt-2">
                                <canvas id="barChartItems2" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
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
                indexAxis: 'y',
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
                            ctx.fillStyle = 'black';
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
        var radarChart = new Chart(ctxRadar, {
            type: 'radar',
            data: {
                labels: domainLabels,
                datasets: [{
                    label: 'Percentual de Habilidades Adquiridas',
                    data: domainPercentages,
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    lineTension: 0
                }, {
                    label: 'Percentual Esperado (100%)',
                    data: fullPercentages,
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: true,
                    lineTension: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                elements: {
                    line: {
                        tension: 0
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            callback: function(value) {
                                return value + '%';
                            },
                            font: {
                                size: 11,
                                weight: 'bold'
                            }
                        },
                        pointLabels: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            lineWidth: 1
                        },
                        angleLines: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            lineWidth: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        display: true,
                        labels: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        },
                        padding: 15,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.r + '%';
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
            datasets: [{
                    label: 'Total Itens',
                    data: domainItemsTotal,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Itens Testados',
                    data: domainItemsTested,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Itens Válidos',
                    data: domainItemsValid,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Itens Inválidos',
                    data: domainItemsInvalid,
                    borderWidth: 1,
                    borderColor: 'rgba(255, 0, 0, 1)', // Cor da borda vermelha
                    backgroundColor: 'rgba(255, 0, 0, 0.5)' // Cor de fundo vermelha com transparência
                },
                {
                    label: 'Percentual (%)',
                    data: domainPercentages,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1,
                    type: 'line',
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
                indexAxis: 'x',
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
                                    return 'rgba(0, 255, 0, 0.3)';
                                } else if (context.tick.value < 0) {
                                    return 'rgba(255, 0, 0, 0.3)';
                                }
                                return '#000000';
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
                            drawOnChartArea: false,
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
        const actions = [{
            name: 'Atualizar Dados',
            handler(chart) {
                // Implemente a lógica para atualizar os dados aqui
                chart.update();
            }
        }];


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


        // Função para capturar as imagens dos gráficos
        function getChartImages() {
            return new Promise((resolve) => {
                // Força a exibição de todas as tabs para garantir que os gráficos sejam renderizados
                document.querySelectorAll('.nav-link').forEach(tab => {
                    const tabPane = document.querySelector(tab.dataset.bsTarget);
                    if (tabPane) {
                        tabPane.classList.add('show', 'active');
                    }
                });

                // Aguarda um pouco mais para garantir a renderização
                setTimeout(() => {
                    try {
                        var barChartCanvas = document.getElementById('barChart');
                        var radarChartCanvas = document.getElementById('radarChart');
                        var barChartItems2Canvas = document.getElementById('barChartItems2');

                        var barChartImage = barChartCanvas ? barChartCanvas.toDataURL('image/png', 1.0) :
                            null;
                        var radarChartImage = radarChartCanvas ? radarChartCanvas.toDataURL('image/png',
                            1.0) : null;
                        var barChartItems2Image = barChartItems2Canvas ? barChartItems2Canvas.toDataURL(
                            'image/png', 1.0) : null;

                        // Restaura a visualização original
                        document.querySelectorAll('.nav-link').forEach(tab => {
                            const tabPane = document.querySelector(tab.dataset.bsTarget);
                            if (tabPane) {
                                tabPane.classList.remove('show', 'active');
                            }
                        });

                        // Reativa a tab inicial
                        document.querySelector('#bar-chart').classList.add('show', 'active');

                        resolve({
                            barChartImage: barChartImage || 'data:,',
                            radarChartImage: radarChartImage || 'data:,',
                            barChartItems2Image: barChartItems2Image || 'data:,'
                        });
                    } catch (error) {
                        console.error('Erro ao capturar imagens:', error);
                        resolve({
                            barChartImage: 'data:,',
                            radarChartImage: 'data:,',
                            barChartItems2Image: 'data:,'
                        });
                    }
                }, 1000); // Aumentado para 1 segundo para garantir a renderização
            });
        }

        // Evento para o botão de gerar PDF
        document.getElementById('generatePdfBtn').addEventListener('click', async function(e) {
            e.preventDefault();

            const button = this;
            try {
                // Mostra spinner com texto
                button.disabled = true;
                button.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Gerando PDF...
                `;

                const images = await getChartImages();

                document.getElementById('barChartImageInput').value = images.barChartImage;
                document.getElementById('radarChartImageInput').value = images.radarChartImage;
                document.getElementById('barChartItems2ImageInput').value = images.barChartItems2Image;

                // Verifica se as imagens foram geradas corretamente
                if (images.barChartImage === 'data:,' ||
                    images.radarChartImage === 'data:,' ||
                    images.barChartItems2Image === 'data:,') {
                    throw new Error('Falha ao gerar uma ou mais imagens dos gráficos');
                }

                document.getElementById('pdfForm').submit();
            } catch (error) {
                console.error('Erro ao gerar PDF:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Ocorreu um erro ao gerar o PDF. Por favor, tente novamente.'
                });
            } finally {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-filetype-pdf"></i> Gerar PDF';
            }
        });
    </script>
@endpush
