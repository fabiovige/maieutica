@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checklists</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            @can('create checklists')
                <a href="{{ route('checklists.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Cadastrar novo checklist
                </a>
            @endcan
        </div>
        <div class="card-body">
            <div class="row" id="app">
                @if (isset($kid))
                    <div class="col-md-4">
                        <Resume :responsible="{{ $kid->responsible()->first() }}"
                            :professional="{{ $kid->professional()->first() }}" :kid="{{ $kid }}"
                            :checklist="{{ $kid->checklists()->count() }}" :plane="{{ $kid->planes()->count() }}"
                            :months="{{ $kid->months }}">
                        </Resume>
                    </div>
                @endif
                <div class="{{ isset($kid) ? 'col-md-8' : 'col-md-12' }} mt-2">
                    <h3>Checklists</h3>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Checklist ID</th>
                                @if (!isset($kid))<th>Criança</th>@endif
                                <th>Status</th>
                                <th>Data de criação</th>
                                <th>Média Geral do Desenvolvimento</th>
                                <th style="width: 100px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($checklists as $checklist)
                                <tr>
                                    <td>{{ $checklist->id }}</td>
                                    @if (!isset($kid))<td>{{ $checklist->kid->name }}</td>@endif
                                    <td>{{ $checklist->situation_label }}</td>
                                    <td>{{ $checklist->created_at }}</td>
                                    <td>

                                        <div class="progress" role="progressbar" aria-label="checklist{{$checklist->id}}" aria-valuenow="{{$checklist->developmentPercentage}}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" style="width: {{$checklist->developmentPercentage}}%"></div>
                                        </div>

                                        {{ $checklist->developmentPercentage }}%
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    id="dropdownMenuButton"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false"
                                                    {{ $checklist->situation_label !== 'Aberto' ? 'disabled' : '' }}>
                                                Ações
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                @if($checklist->situation_label === 'Aberto')
                                                    @can('edit checklists')
                                                        <li><a class="dropdown-item" href="{{ route('checklists.edit', $checklist->id) }}">
                                                            <i class="bi bi-pencil"></i> Anotações
                                                        </a></li>
                                                    @endcan
                                                    @can('fill checklists')
                                                        <li><a class="dropdown-item" href="{{ route('checklists.fill', $checklist->id) }}">
                                                            <i class="bi bi-check2-square"></i> Avaliação
                                                        </a></li>
                                                    @endcan
                                                    @can('fill checklists')
                                                        <li><a class="dropdown-item" href="{{ route('kids.showPlane', $checklist->kid->id) }}">
                                                            <i class="bi bi-check2-square"></i> Plano Manual
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="{{ route('kid.plane-automatic', ['kidId' => $checklist->kid->id, 'checklistId' => $checklist->id]) }}">
                                                            <i class="bi bi-file-earmark-pdf"></i> Plano Automático
                                                        </a></li>
                                                    @endcan
                                                    @can('create checklists')
                                                        <li><a class="dropdown-item" href="{{ route('checklists.clonar', $checklist->id) }}">
                                                            <i class="bi bi-copy"></i> Clonar
                                                        </a></li>
                                                    @endcan
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (isset($kid))
                    <div class="col-md-4">
                    </div>
                    <div class="{{ isset($kid) ? 'col-md-8' : 'col-md-12' }} mt-2">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <canvas id="barChart" width="400" height="300"></canvas>
                            </div>
                            <div class="col-md-12">
                                <canvas id="statusChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@push("scripts")

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">

var ctxBar = document.getElementById('barChart').getContext('2d');

var labels = @json($checklists->pluck('id')); // IDs dos checklists como labels

var data = @json($checklists->pluck('developmentPercentage')); // Percentuais de desenvolvimento

var barChart = new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Média Geral do Desenvolvimento (%)',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)', // Azul para as barras
                borderColor: 'rgba(54, 162, 235, 1)', // Azul para bordas das barras
                borderWidth: 1,
                type: 'bar'
            },
            {
                label: 'Linha de Desenvolvimento',
                data: data,
                borderColor: 'rgba(255, 99, 132, 1)', // Vermelho para a linha
                borderWidth: 2,
                fill: false,
                type: 'line',
                tension: 0.3 // Suaviza a linha
            }
        ]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                suggestedMin: 0,
                suggestedMax: 100,
                title: {
                    display: true,
                    text: 'Percentual de Desenvolvimento (%)'
                },
                ticks: {
                    stepSize: 10
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Checklists'
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        responsive: true,
        maintainAspectRatio: false
    }
});

// Novo gráfico de status
var ctxStatus = document.getElementById('statusChart').getContext('2d');

// Preparar dados dos status
var statusLabels = ['Não observado', 'Mais ou menos', 'Difícil de obter', 'Consistente'];

var statusColors = [
    'rgba(108, 117, 125, 0.2)',  // Cinza mais claro para Não observado
    'rgba(255, 193, 7, 0.6)',    // Amarelo para Mais ou menos
    'rgba(220, 53, 69, 0.6)',    // Vermelho para Difícil
    'rgba(40, 167, 69, 0.6)'     // Verde para Consistente
];
var statusBorders = [
    'rgba(108, 117, 125, 1)',
    'rgba(255, 193, 7, 1)',
    'rgba(220, 53, 69, 1)',
    'rgba(40, 167, 69, 1)'
];

var statusChart = new Chart(ctxStatus, {
    type: 'bar',
    data: {
        labels: statusLabels,
        datasets: [{
            label: 'Distribuição de Competências por Status',
            data: statusData,
            backgroundColor: statusColors,
            borderColor: statusBorders,
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Quantidade de Competências'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Status de Avaliação'
                }
            }
        },
        plugins: {
            legend: {
                display: false  // Removido pois só temos um dataset
            },
            title: {
                display: true,
                text: 'Distribuição das Competências por Status',
                font: {
                    size: 16
                }
            }
        },
        responsive: true,
        maintainAspectRatio: false
    }
});

</script>

@endpush
