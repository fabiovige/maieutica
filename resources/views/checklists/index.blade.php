@extends('layouts.app')

@section('title')
    Checklists
@endsection

@section('breadcrumb-items')
    @if ($kid)
        <li class="breadcrumb-item">

            <a href="{{ route('kids.index') }}">
                <i class="bi bi-people"></i> Crianças
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            <i class="bi bi-card-checklist"></i> Checklists
        </li>
    @else
        <li class="breadcrumb-item active" aria-current="page">
            <i class="bi bi-card-checklist"></i> Checklists
        </li>
    @endif
    </li>
@endsection

@section('actions')
    @can('checklist-create')
        @if ($kid)
            <button onclick="openDateModal()" class="btn btn-primary">
                <span class="d-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i>
                    <span class="button-text">Novo Checklist</span>
                </span>
            </button>

            <!-- Modal para seleção de tipo de checklist -->
            <div class="modal fade" id="dateModal" tabindex="-1" aria-labelledby="dateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="dateModalLabel">Criar Checklist</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Checklist</label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="checklistType" id="checklistTypeAtual" value="atual" checked>
                                        <label class="form-check-label" for="checklistTypeAtual">
                                            Checklist com base no atual
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="checklistType" id="checklistTypeRetro" value="retro">
                                        <label class="form-check-label" for="checklistTypeRetro">
                                            Checklist com data retroativa
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3" id="retroactiveDateGroup" style="display: none;">
                                <label for="retroactiveDate" class="form-label">Data do Checklist</label>
                                <input type="date" class="form-control" id="retroactiveDate" max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="confirmDateBtn">Criar Checklist</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function openDateModal() {
                    var dateModal = new bootstrap.Modal(document.getElementById('dateModal'));
                    document.getElementById('retroactiveDate').value = '';
                    document.getElementById('checklistTypeAtual').checked = true;
                    document.getElementById('retroactiveDateGroup').style.display = 'none';
                    dateModal.show();
                }

                document.addEventListener('DOMContentLoaded', function () {
                    window.kidId = "{{ $kid->id }}";
                    // Alternar exibição do campo de data
                    document.getElementById('checklistTypeAtual').addEventListener('change', function () {
                        document.getElementById('retroactiveDateGroup').style.display = 'none';
                    });
                    document.getElementById('checklistTypeRetro').addEventListener('change', function () {
                        document.getElementById('retroactiveDateGroup').style.display = 'block';
                    });

                    document.getElementById('confirmDateBtn').addEventListener('click', function () {
                        var type = document.querySelector('input[name="checklistType"]:checked').value;
                        if (type === 'retro') {
                            var date = document.getElementById('retroactiveDate').value;
                            if (!date) {
                                alert('Por favor, selecione uma data.');
                                return;
                            }
                            createChecklistWithDate(date, this);
                        } else {
                            // Cria checklist com data de hoje
                            createChecklistWithDate(null, this);
                        }
                    });
                });

                function createChecklistWithDate(date, button) {
                    button.disabled = true;
                    const buttonContent = button.innerHTML;
                    button.innerHTML = `
                        <span class=\"d-flex align-items-center\">
                            <span class=\"spinner-border spinner-border-sm me-2\" role=\"status\" aria-hidden=\"true\"></span>
                            Criando...
                        </span>
                    `;

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    let bodyData = {
                        kid_id: window.kidId,
                        level: 4
                    };
                    if (date) {
                        bodyData.created_at = date;
                    }
                    fetch("{{ route('checklists.store', ['kidId' => $kid->id]) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(bodyData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                button.disabled = false;
                                button.innerHTML = buttonContent;
                                alert('Erro ao criar checklist: ' + (data.error || 'Erro desconhecido'));
                            }
                        })
                        .catch(error => {
                            button.disabled = false;
                            button.innerHTML = buttonContent;
                            alert('Erro ao criar checklist: ' + error.message);
                        });
                }
            </script>
        @endif
    @endcan
@endsection

@section('content')
    <div class="row mb-4">
        @if (isset($kid))
            <div class="col-md-12">
                <x-kid-info-card :kid="$kid" />
            </div>
        @endif
    </div>

    <!-- Filtro de Busca -->
    @if (!isset($kid))
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('checklists.index') }}" class="row g-3">
                    <div class="col-md-10">
                        <label for="search" class="form-label">
                            <i class="bi bi-search"></i> Buscar Checklist
                        </label>
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               placeholder="Buscar por criança, ID do checklist..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            @if(request('search'))
                                <a href="{{ route('checklists.index') }}" class="btn btn-secondary" title="Limpar filtro">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(request('search'))
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Exibindo resultados da busca por "<strong>{{ request('search') }}</strong>".
                <strong>{{ $checklists->count() }}</strong> checklist(s) encontrado(s).
            </div>
        @endif
    @endif

    <div class="row">
        <div class="{{ isset($kid) ? 'col-md-6' : 'col-md-12' }}">

            <table class="table table-hover table-bordered align-middle mt-3">
                <thead>
                    <tr>
                        <th style="width: 60px;" class="text-center align-middle">ID</th>
                        @if (!isset($kid))
                            <th>Criança</th>
                        @endif
                        <th>Status</th>
                        <th>Data de criação</th>
                        <th>Média Geral do Desenvolvimento</th>
                        @can('edit checklists')
                            <th width="100">Ações</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @if ($checklists->isEmpty())
                        <tr>
                            <td colspan="{{ isset($kid) ? '5' : '6' }}" class="text-center">
                                Nenhum checklist encontrado.
                            </td>
                        </tr>
                    @else
                        @foreach ($checklists as $checklist)
                            <tr>
                                <td class="text-center">{{ $checklist->id }}</td>
                                @if (!isset($kid))
                                    <td>{{ $checklist->kid->name }}</td>
                                @endif
                                <td><span
                                        class="badge {{ $checklist->situation_label === 'Aberto' ? 'bg-success' : 'bg-secondary' }}">{{ $checklist->situation_label }}</span>
                                </td>
                                <td>{{ $checklist->created_at }}</td>
                                <td>

                                    <div class="progress" role="progressbar" aria-label="checklist{{ $checklist->id }}"
                                        aria-valuenow="{{ $checklist->developmentPercentage }}" aria-valuemin="0"
                                        aria-valuemax="100">
                                        <div class="progress-bar" style="width: {{ $checklist->developmentPercentage }}%; background-color: {{ get_progress_color($checklist->developmentPercentage) }} !important">
                                        </div>
                                    </div>

                                    {{ $checklist->developmentPercentage }}%
                                </td>

                                    <td>
                                        <div class="dropdown">
                                            @php
                                                $isAdmin = auth()->check() && auth()->user()->can('checklist-edit-all');
                                            @endphp
                                            @can('checklist-edit')
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false"
                                                    @if($checklist->situation_label !== 'Aberto' && !$isAdmin) disabled @endif>
                                                    Ações
                                                </button>
                                            @endcan
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                                    @can('checklist-edit')
                                                        <li><a class="dropdown-item"
                                                                href="{{ isset($kid) ? route('checklists.edit', ['checklist' => $checklist->id, 'kidId' => $kid->id]) : route('checklists.edit', $checklist->id) }}">
                                                                <i class="bi bi-pencil"></i> Editar
                                                            </a></li>
                                                    @endcan
                                                    @can('checklist-avaliation')
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('checklists.fill', $checklist->id) }}">
                                                                <i class="bi bi-check2-square"></i> Avaliação
                                                            </a></li>
                                                    @endcan
                                                    @can('checklist-plane-manual')
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('kids.showPlane', $checklist->kid->id) }}">
                                                                <i class="bi bi-check2-square"></i> Plano Manual
                                                            </a></li>
                                                    @endcan
                                                    @can('checklist-plane-automatic')
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('kid.plane-automatic', ['kidId' => $checklist->kid->id, 'checklistId' => $checklist->id]) }}">
                                                                <i class="bi bi-file-earmark-pdf"></i> Plano Automático
                                                            </a></li>
                                                    @endcan
                                                    @can('checklist-clone')
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('checklists.clonar', ['id' => $checklist->id, 'kid_id' => $checklist->kid_id]) }}">
                                                                <i class="bi bi-copy"></i> Clonar
                                                            </a></li>
                                                    @endcan

                                            </ul>
                                        </div>
                                    </td>

                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript">
        var ctxBar = document.getElementById('barChart').getContext('2d');

        // Formatar as datas dos checklists para labels
        var labels = @json(
            $checklists->map(function ($checklist) {
                return ['Checklist #' . $checklist->id, $checklist->created_at->format('d/m/Y')];
            }));

        var data = @json($checklists->pluck('developmentPercentage')); // Percentuais de desenvolvimento

        var barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labels.map(l => l[0] + '\n' + l[1]), // Mostra ID e data em duas linhas
                datasets: [{
                        label: 'Média Geral do Desenvolvimento (%)',
                        data: data,
                        backgroundColor: data.map(value => {
                            if (value < 30)
                                return 'rgba(220, 53, 69, 0.6)'; // Vermelho para baixo desenvolvimento
                            if (value < 70)
                                return 'rgba(255, 193, 7, 0.6)'; // Amarelo para médio desenvolvimento
                            return 'rgba(40, 167, 69, 0.6)'; // Verde para alto desenvolvimento
                        }),
                        borderWidth: 1,
                        type: 'bar'
                    },
                    {
                        label: 'Linha de Desenvolvimento',
                        data: data,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false,
                        type: 'line',
                        tension: 0.3
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
                            text: 'Avaliações por Data'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
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
                                if (context.dataset.type === 'bar') {
                                    return `Desenvolvimento: ${context.raw.toFixed(1)}%`;
                                }
                                return context.dataset.label + ': ' + context.raw.toFixed(1) + '%';
                            }
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Preparar dados dos status
        @php
            $statusData = [0, 0, 0, 0]; // Valores padrão caso não haja checklist

            if ($checklists->isNotEmpty()) {
                // Pegar apenas o último checklist
                $lastChecklist = $checklists->first(); // Já está ordenado por created_at desc

                // Contar as competências por nota
                $statusData = [
                    $lastChecklist->competences->where('pivot.note', 0)->count(), // Não observado
                    $lastChecklist->competences->where('pivot.note', 1)->count(), // Mais ou menos
                    $lastChecklist->competences->where('pivot.note', 2)->count(), // Difícil
                    $lastChecklist->competences->where('pivot.note', 3)->count(), // Consistente
                ];
            }
        @endphp

        var statusData = @json($statusData);

        // Configuração do gráfico de status
        var ctxStatus = document.getElementById('statusChart').getContext('2d');
        var statusLabels = ['Não observado', 'Em desenvolvimento', 'Não desenvolvido', 'Desenvolvido'];

        var statusColors = [
            'rgba(108, 117, 125, 0.2)', // Cinza para Não observado
            'rgba(255, 193, 7, 0.6)', // Amarelo para Em desenvolvimento
            'rgba(220, 53, 69, 0.6)', // Vermelho para Não desenvolvido
            'rgba(40, 167, 69, 0.6)' // Verde para Desenvolvido
        ];

        var statusBorders = [
            'rgba(108, 117, 125, 1)', // Cinza para Não observado
            'rgba(255, 193, 7, 1)', // Amarelo para Em desenvolvimento
            'rgba(220, 53, 69, 1)', // Vermelho para Não desenvolvido
            'rgba(40, 167, 69, 1)' // Verde para Desenvolvido
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
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Status de Desenvolvimento - Última Avaliação',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.raw} habilidades (${percentage}%)`;
                            }
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
@endpush
