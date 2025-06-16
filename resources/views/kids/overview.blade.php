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

    {{-- Verificar se há checklist disponível --}}
    @if(!isset($currentChecklist) || !$currentChecklist)
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="py-5">
                    <i class="bi bi-clipboard-x text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3 text-muted">Nenhum Checklist Encontrado</h3>
                    <p class="text-muted mb-4">
                        Esta criança ainda não possui um checklist avaliado.<br>
                        É necessário criar e avaliar um checklist para visualizar o desenvolvimento.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('kids.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar às Crianças
                        </a>
                        <a href="{{ route('kids.show', $kid->id) }}" class="btn btn-primary">
                            <i class="bi bi-clipboard-plus"></i> Ver Perfil da Criança
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Informações da Criança</h3>

                        <!-- Seletor de Checklist -->
                        <div class="form-group mb-3">
                            <label for="checklistSelect"><strong>Selecionar Checklist:</strong></label>
                            <select id="checklistSelect" name="checklist_id" class="form-control" onchange="changeChecklist(this.value)">
                                @foreach ($allChecklists as $checklist)
                                    <option value="{{ $checklist->id }}" {{
                                        ($checklistId && $checklistId == $checklist->id) ||
                                        (!$checklistId && $currentChecklist->id == $checklist->id) ? 'selected' : ''
                                    }}>
                                        Checklist #{{ $checklist->id }} - {{ $checklist->created_at->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if ($currentChecklist)
                            <p><strong>Checlist Sendo Exibido:</strong>
                                Checklist #{{ $currentChecklist->id }} - {{ $currentChecklist->created_at->format('d/m/Y') }}</p>
                        @else
                            <p><strong>Checklist Atual:</strong> Não disponível</p>
                        @endif

                        <p><strong>Idade de Desenvolvimento:</strong> {{ round($developmentalAgeInMonths, 0) }} meses</p>
                        <p><strong>Atraso:</strong> {{ round($delayInMonths, 0) }} meses</p>

                        <p><button id="generatePdfBtn" class="btn btn-primary mt-3"><i class="bi bi-filetype-pdf"></i> Gerar
                                PDF</button></p>

                        <form id="pdfForm"
                            action="{{ route('kids.generatePdf', ['kidId' => $kid->id, 'levelId' => $levelId]) }}{{ $checklistId ? '?checklist_id=' . $checklistId : '' }}"
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
                            <div class="progress-bar progress-bar-striped"
                                style="width: {{ $averagePercentage }}%; background: {{ get_progress_gradient($averagePercentage) }}">
                                {{ $averagePercentage }}%
                            </div>
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
                                                                style="width: {{ $domain['percentage'] }}%; background: {{ get_progress_gradient($domain['percentage']) }}">
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
                                                <th>{{ round($totalPercentage, 2) }}%</th>
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
                                                                <div class="progress-bar"
                                                                    style="width: {{ $area['percentage'] }}%; background: {{ get_progress_gradient($area['percentage']) }}">
                                                                </div>
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
    @endif

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Registrar o plugin
        Chart.register(ChartDataLabels);

        // Verificar se os dados dos domínios existem antes de renderizar os gráficos
        @if(isset($domainData) && count($domainData) > 0)
            const domainData = @json($domainData);
            const domainNames = domainData.map(domain => domain.name);
            const domainPercentages = domainData.map(domain => domain.percentage);
            const validItems = domainData.map(domain => domain.itemsValid);
            const invalidItems = domainData.map(domain => domain.itemsInvalid);
            const totalItems = domainData.map(domain => domain.itemsTotal);
            const testedItems = domainData.map(domain => domain.itemsTested);

            // Cores do enum ProgressColors
            const progressColors = {
                0: '{{ get_progress_color(0) }}',
                10: '{{ get_progress_color(10) }}',
                20: '{{ get_progress_color(20) }}',
                30: '{{ get_progress_color(30) }}',
                40: '{{ get_progress_color(40) }}',
                50: '{{ get_progress_color(50) }}',
                60: '{{ get_progress_color(60) }}',
                70: '{{ get_progress_color(70) }}',
                80: '{{ get_progress_color(80) }}',
                90: '{{ get_progress_color(90) }}',
                100: '{{ get_progress_color(100) }}'
            };

            // Função para obter a cor baseada no percentual
            function getProgressColor(percentage) {
                const rounded = Math.round(percentage / 10) * 10;
                const safe = Math.max(0, Math.min(100, rounded));
                return progressColors[safe];
            }

            // Função para adicionar transparência à cor
            function addTransparency(color, alpha) {
                return color + alpha;
            }

            var ctxBar = document.getElementById('barChart').getContext('2d');
            var ctxRadar = document.getElementById('radarChart').getContext('2d');
            var ctxBarItems2 = document.getElementById('barChartItems2').getContext('2d');

            var barColors = domainPercentages.map(percentage => getProgressColor(percentage));

            var barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: domainNames,
                    datasets: [{
                        label: 'Percentual de Habilidades Adquiridas',
                        data: domainPercentages,
                        backgroundColor: barColors,
                        borderColor: barColors,
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
                        },
                        datalabels: {
                            color: '#000',
                            anchor: 'end',
                            align: 'right',
                            offset: 4,
                            formatter: function(value) {
                                return value + '%';
                            },
                            font: {
                                weight: 'bold'
                            },
                            padding: {
                                right: 4
                            }
                        }
                    }
                }
            });

            // Radar Chart
            var radarChart = new Chart(ctxRadar, {
                type: 'radar',
                data: {
                    labels: domainNames,
                    datasets: [{
                        label: 'Percentual de Habilidades Adquiridas',
                        data: domainPercentages,
                        backgroundColor: barColors.map(color => addTransparency(color, '80')),
                        borderColor: barColors,
                        pointBackgroundColor: barColors,
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: barColors,
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
                                }
                            }
                        }
                    }
                }
            });

            // Bar Chart Items 2
            var barChartItems2 = new Chart(ctxBarItems2, {
                type: 'bar',
                data: {
                    labels: domainNames,
                    datasets: [{
                        label: 'Total Itens',
                        data: totalItems,
                        backgroundColor: barColors.map(color => addTransparency(color, '80')),
                        borderColor: barColors,
                        borderWidth: 1
                    },
                    {
                        label: 'Itens Testados',
                        data: testedItems,
                        backgroundColor: barColors.map(color => addTransparency(color, '60')),
                        borderColor: barColors,
                        borderWidth: 1
                    },
                    {
                        label: 'Itens Válidos',
                        data: validItems,
                        backgroundColor: barColors.map(color => addTransparency(color, '40')),
                        borderColor: barColors,
                        borderWidth: 1
                    },
                    {
                        label: 'Itens Inválidos',
                        data: invalidItems,
                        backgroundColor: barColors.map(color => addTransparency(color, '20')),
                        borderColor: barColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true
                        }
                    }
                }
            });
        @endif

        function changeLevel(selectedLevel) {
            var kidId = {{ $kid->id }};
            var checklistId = "{{ $checklistId ?? '' }}";
            var baseUrl = "{{ url('kids') }}";
            var url = '';

            if (selectedLevel) {
                // Se um nível específico for selecionado
                url = baseUrl + "/" + kidId + "/level/" + selectedLevel + "/overview";
            } else {
                // Se "Todos os Níveis" for selecionado
                url = baseUrl + "/" + kidId + "/overview";
            }

            // Adicionar checklistId se existir
            if (checklistId) {
                url += "?checklist_id=" + checklistId;
            }

            // Redirecionar para a URL construída
            window.location.href = url;
        }

        function changeChecklist(checklistId) {
            var kidId = {{ $kid->id }};
            var levelId = "{{ $levelId ?? '' }}";
            var baseUrl = "{{ url('kids') }}";
            var url = baseUrl + "/" + kidId;

            // Adicionar nível se selecionado
            if (levelId) {
                url += "/level/" + levelId;
            }

            url += "/overview";

            // Adicionar checklist - sempre adiciona pois agora sempre teremos um ID
            url += "?checklist_id=" + checklistId;

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

@endsection
