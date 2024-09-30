<!-- resources/views/kids/teste.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalhes da Criança: {{ $child->name }}</h1>
    <p>Idade: {{ $ageInMonths }} meses</p>

    <!-- Gráfico de Linhas -->
    <canvas id="developmentChart" width="1200" height="600"></canvas>

    <!-- Gráfico de Pizza -->
    <h2 class="mt-5">Desempenho no Nível 2</h2>
    <canvas id="level2Chart" width="400" height="400"></canvas>

    <!-- Gráfico de Barras com Linha de Evolução -->
    <h2 class="mt-5">Comparação de Percentis por Competência</h2>
    <canvas id="percentileComparisonChart" width="1200" height="600"></canvas>

    <!-- Incluir o Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico de Linhas
        var ctx = document.getElementById('developmentChart').getContext('2d');

        var competenceLabels = @json($competenceLabels);
        var childScores = @json($childScores);
        var percentil25 = @json($percentil25);
        var percentil50 = @json($percentil50);
        var percentil75 = @json($percentil75);
        var percentil90 = @json($percentil90);
        var ageInMonths = {{ $ageInMonths }};
        var pointColors = @json($pointColors);
        var status = @json($status);

        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: competenceLabels,
                datasets: [
                    {
                        label: 'Resultado da Criança (N=0, P=1, A=2)',
                        data: childScores,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                        yAxisID: 'y',
                        tension: 0.1,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: pointColors,
                        pointBorderColor: 'rgba(0, 0, 0, 0.1)',
                        pointBorderWidth: 1,
                    },
                    {
                        label: 'Percentil 25%',
                        data: percentil25,
                        borderColor: 'rgb(255, 159, 64)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        fill: false,
                        yAxisID: 'y1',
                        tension: 0.1,
                        borderDash: [5, 5],
                    },
                    {
                        label: 'Percentil 50%',
                        data: percentil50,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: false,
                        yAxisID: 'y1',
                        tension: 0.1,
                        borderDash: [5, 5],
                    },
                    {
                        label: 'Percentil 75%',
                        data: percentil75,
                        borderColor: 'rgb(153, 102, 255)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        fill: false,
                        yAxisID: 'y1',
                        tension: 0.1,
                        borderDash: [5, 5],
                    },
                    {
                        label: 'Percentil 90%',
                        data: percentil90,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: false,
                        yAxisID: 'y1',
                        tension: 0.1,
                        borderDash: [5, 5],
                    },
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label.includes('Percentil')) {
                                    return label + ': ' + context.parsed.y + ' meses';
                                } else {
                                    switch(context.parsed.y) {
                                        case 0:
                                            return label + ': N (Não adquirido)';
                                        case 1:
                                            return label + ': P (Parcial)';
                                        case 2:
                                            return label + ': A (Adquirido)';
                                        default:
                                            return label + ': ' + context.parsed.y;
                                    }
                                }
                            }
                        }
                    },
                    legend: {
                        display: false, // Oculta a legenda original para personalização
                    },
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Resultado da Competência'
                        },
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                if (value === 0) return 'N';
                                if (value === 1) return 'P';
                                if (value === 2) return 'A';
                                return value;
                            }
                        },
                        min: 0,
                        max: 2,
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Idade Esperada (meses)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 2,
                        },
                        min: 20,
                        max: ageInMonths + 6, // Ajuste conforme necessário
                    },
                },
            }
        });

        // Gráfico de Pizza para Nível 2
        var ctxPie = document.getElementById('level2Chart').getContext('2d');

        var level2Labels = ['Adiantada', 'No Prazo', 'Atrasada'];
        var level2Data = [
            @json($level2StatusCounts['Adiantada']),
            @json($level2StatusCounts['No Prazo']),
            @json($level2StatusCounts['Atrasada']),
        ];

        var pieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: level2Labels,
                datasets: [{
                    label: 'Desempenho no Nível 2',
                    data: level2Data,
                    backgroundColor: [
                        'rgb(75, 192, 192)',    // Adiantada - Verde-água
                        'rgb(54, 162, 235)',    // No Prazo - Azul
                        'rgb(255, 99, 132)',    // Atrasada - Vermelho
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed;
                                let total = context.chart._metasets[context.datasetIndex].total;
                                let percentage = ((value / total) * 100).toFixed(2) + '%';
                                return label + ': ' + percentage;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                    },
                },
            }
        });

        // Gráfico de Barras com Linha de Evolução
        var ctxBar = document.getElementById('percentileComparisonChart').getContext('2d');

        var idealPercentiles = @json($idealPercentiles);
        var childPercentiles = @json($childPercentiles);
        var averageChildPercentile = @json($averageChildPercentile);

        var percentileComparisonChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: competenceLabels,
                datasets: [
                    {
                        label: 'Percentil Ideal (50%)',
                        data: idealPercentiles,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)', // Verde-água
                        borderColor: 'rgb(75, 192, 192)',
                        borderWidth: 1,
                        yAxisID: 'yPercentile',
                    },
                    {
                        label: 'Percentil da Criança',
                        data: childPercentiles,
                        backgroundColor: 'rgba(255, 159, 64, 0.5)', // Laranja
                        borderColor: 'rgb(255, 159, 64)',
                        borderWidth: 1,
                        yAxisID: 'yPercentile',
                    },
                    {
                        label: 'Média Percentil da Criança',
                        data: competenceLabels.map(() => averageChildPercentile),
                        type: 'line',
                        fill: false,
                        borderColor: 'rgb(255, 99, 132)', // Vermelho
                        borderWidth: 2,
                        pointRadius: 0,
                        yAxisID: 'yPercentile',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.type === 'line') {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
                                }
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                },
                scales: {
                    yPercentile: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Percentil (%)'
                        },
                        ticks: {
                            beginAtZero: true,
                            max: 100,
                            stepSize: 10,
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                    },
                },
            }
        });

        // Adicionar uma legenda customizada para os status no Gráfico de Linhas
        var legendContainer = document.createElement('div');
        legendContainer.style.marginTop = '20px';
        legendContainer.innerHTML = `
            <h4>Legenda de Status</h4>
            <p><span style="display:inline-block;width:12px;height:12px;background-color:rgb(75, 192, 192);margin-right:5px;"></span> Adiantada</p>
            <p><span style="display:inline-block;width:12px;height:12px;background-color:rgb(54, 162, 235);margin-right:5px;"></span> No Prazo</p>
            <p><span style="display:inline-block;width:12px;height:12px;background-color:rgb(255, 99, 132);margin-right:5px;"></span> Atrasada</p>
        `;
        ctx.parentNode.appendChild(legendContainer);
    </script>
</div>
@endsection
