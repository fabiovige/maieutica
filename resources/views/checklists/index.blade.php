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

@section('title')
    Checklists {{ $kid ? '- ' . $kid->name : '' }}
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('kids.index') }}">
            <i class="bi bi-people"></i> Crianças
        </a>
    </li>
    @if($kid)
    <li class="breadcrumb-item">
        <a href="{{ route('kids.edit', $kid->id) }}">{{ $kid->name }}</a>
    </li>
    @endif
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-card-checklist"></i> Checklists
    </li>
@endsection

@section('actions')
    @can('create checklists')
        @if($kid)
            <button onclick="createChecklist()" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Novo Checklist
            </button>

            <script>
                function createChecklist() {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    fetch("{{ route('checklists.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            kid_id: {{ $kid->id }},
                            level: 4
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Erro ao criar checklist: ' + (data.error || 'Erro desconhecido'));
                        }
                    })
                    .catch(error => {
                        alert('Erro ao criar checklist: ' + error.message);
                    });
                }
            </script>
        @endif
    @endcan
@endsection

@section('content')

    <div class="row mb-4">
        @if(isset($kid))
            <div class="col-md-12">
                <x-kid-info-card :kid="$kid" />
            </div>
        @endif
    </div>

    <div class="row">
        <div class="{{ isset($kid) ? 'col-md-6' : 'col-md-6' }}">
            <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mt-3">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        @if (!isset($kid))<th>Criança</th>@endif
                        <th>Status</th>
                        <th>Data de criação</th>
                        <th>Média Geral do Desenvolvimento</th>
                        <th style="width: 100px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($checklists as $checklist)
                        <tr>
                            <td>{{ $checklist->id }}</td>
                            @if (!isset($kid))<td>{{ $checklist->kid->name }}</td>@endif
                            <td><span class="badge {{ $checklist->situation_label === 'Aberto' ? 'bg-success' : 'bg-secondary' }}">{{ $checklist->situation_label }}</span></td>
                            <td>{{ $checklist->created_at->format('d/m/Y') }}</td>
                            <td>

                                <div class="progress" role="progressbar" aria-label="checklist{{$checklist->id}}" aria-valuenow="{{$checklist->developmentPercentage}}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{$checklist->developmentPercentage}}%"></div>
                                </div>

                                {{ $checklist->developmentPercentage }}%
                            </td>
                            @can('edit checklists')
                            <td>
                                <div class="dropdown">
                                    @can('edit checklists')
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            {{ $checklist->situation_label !== 'Aberto' ? 'disabled' : '' }}>
                                        Ações
                                    </button>
                                    @endcan
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        @if($checklist->situation_label === 'Aberto')
                                            @can('edit checklists')
                                                <li><a class="dropdown-item" href="{{ route('checklists.edit', $checklist->id) }}">
                                                    <i class="bi bi-pencil"></i> Editar
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
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        @if (isset($kid))
            <div class="{{ isset($kid) ? 'col-md-6' : 'col-md-6' }} mt-2">
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

// Preparar dados dos status
const statusData = [
    {{ $checklists->flatMap(function($checklist) {
        return $checklist->competences->pluck('pivot.note');
    })->filter(function($note) { return $note === 0; })->count() }}, // Não observado
    {{ $checklists->flatMap(function($checklist) {
        return $checklist->competences->pluck('pivot.note');
    })->filter(function($note) { return $note === 1; })->count() }}, // Mais ou menos
    {{ $checklists->flatMap(function($checklist) {
        return $checklist->competences->pluck('pivot.note');
    })->filter(function($note) { return $note === 2; })->count() }}, // Difícil
    {{ $checklists->flatMap(function($checklist) {
        return $checklist->competences->pluck('pivot.note');
    })->filter(function($note) { return $note === 3; })->count() }}  // Consistente
];

// Configuração do gráfico de status
var ctxStatus = document.getElementById('statusChart').getContext('2d');
var statusLabels = ['Não observado', 'Mais ou menos', 'Difícil de obter', 'Consistente'];

var statusColors = [
    'rgba(108, 117, 125, 0.2)',
    'rgba(255, 193, 7, 0.6)',
    'rgba(220, 53, 69, 0.6)',
    'rgba(40, 167, 69, 0.6)'
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
