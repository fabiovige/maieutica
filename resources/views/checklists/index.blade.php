@extends('layouts.app')

@section('title')
    Checklists
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-card-checklist"></i> Checklists
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
        @else
            <a href="{{ route('checklists.create') }}" class="btn btn-primary">
                <span class="d-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i>
                    <span class="button-text">Novo Checklist</span>
                </span>
            </a>
        @endif
    @endcan
@endsection

@push('styles')
<style>
    .checklist-item-card {
        border-radius: 12px !important;
        transition: box-shadow 0.2s ease, transform 0.15s ease;
    }
    .checklist-item-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.10) !important;
        transform: translateY(-1px);
    }
    .checklist-progress {
        height: 6px;
        border-radius: 4px;
        min-width: 80px;
        max-width: 160px;
    }
    @media (max-width: 575px) {
        .checklist-item-card .card-body {
            padding: 0.85rem 1rem !important;
        }
        .checklist-meta {
            font-size: 0.8125rem;
        }
    }
</style>
@endpush

@section('content')
    <div class="row">
        @if (isset($kid))
            <div class="col-md-12 mb-3">
                <x-kid-info-card :kid="$kid" />
            </div>
        @endif
    </div>

    {{-- Filtro de Busca --}}
    @if (!isset($kid))
        <div class="card mb-3 border-0 shadow-sm" style="border-radius:12px;">
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
                Exibindo resultados para "<strong>{{ request('search') }}</strong>".
                <strong>{{ $checklists->count() }}</strong> checklist(s) encontrado(s).
            </div>
        @endif
    @endif

    {{-- Lista de Checklists em Cards --}}
    <div class="d-flex flex-column gap-2">
        @forelse($checklists as $checklist)
            @php $isOpen = $checklist->situation_label === 'Aberto'; @endphp
            <div class="card shadow-sm border-0 checklist-item-card">

                {{-- Corpo: informações do checklist --}}
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-center gap-3">

                        {{-- Lado esquerdo: informações --}}
                        <div class="d-flex flex-wrap align-items-center gap-3 flex-grow-1 checklist-meta">

                            {{-- Status --}}
                            <span class="badge {{ $isOpen ? 'bg-success' : 'bg-secondary opacity-75' }} px-2 py-1">
                                <i class="bi {{ $isOpen ? 'bi-unlock' : 'bi-lock' }}"></i>
                                {{ $checklist->situation_label }}
                            </span>

                            {{-- Nome da criança --}}
                            @if(!isset($kid))
                                <span class="fw-semibold text-dark">
                                    {{ $checklist->kid->name ?? 'N/D' }}
                                </span>
                            @endif

                            {{-- Nível --}}
                            <span class="badge bg-primary-subtle text-primary-emphasis px-2">
                                <i class="bi bi-layers"></i> Nível {{ $checklist->level }}
                            </span>

                            {{-- Idade da criança --}}
                            @if($checklist->kid)
                                <span class="text-muted small">
                                    <i class="bi bi-person-fill me-1"></i>{{ $checklist->kid->FullNameMonths ?? 'N/D' }}
                                </span>
                            @endif

                            {{-- Barra de progresso --}}
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress checklist-progress flex-grow-1">
                                    <div class="progress-bar"
                                         role="progressbar"
                                         style="width: {{ $checklist->developmentPercentage }}%; background-color: {{ get_progress_color($checklist->developmentPercentage) }} !important;"
                                         aria-valuenow="{{ $checklist->developmentPercentage }}"
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <span class="text-muted small fw-semibold" style="min-width:36px;">
                                    {{ $checklist->developmentPercentage }}%
                                </span>
                            </div>

                            {{-- Data de criação --}}
                            <span class="text-muted small">
                                <i class="bi bi-calendar3 me-1"></i>{{ $checklist->created_at->format('d/m/Y') }}
                            </span>
                        </div>

                        {{-- Lado direito: botão Ver --}}
                        <div class="flex-shrink-0">
                            <a href="{{ route('checklists.show', $checklist->id) }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        @empty
            <div class="alert alert-info border-0 shadow-sm" style="border-radius:12px;">
                <i class="bi bi-info-circle"></i> Nenhum checklist encontrado.
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $checklists->links() }}
    </div>

    {{-- Gráficos (apenas no contexto de kid) --}}
    @if (isset($kid))
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <canvas id="barChart" height="300"></canvas>
        </div>
        <div class="col-md-6 mb-4">
            <canvas id="statusChart" height="300"></canvas>
        </div>
    </div>
    @endif

    @can('checklist-create')
        @if ($kid)
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
                            createChecklistWithDate(null, this);
                        }
                    });
                });

                function createChecklistWithDate(date, button) {
                    button.disabled = true;
                    const buttonContent = button.innerHTML;
                    button.innerHTML = `
                        <span class="d-flex align-items-center">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Criando...
                        </span>
                    `;

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    let bodyData = { kid_id: window.kidId, level: 4 };
                    if (date) { bodyData.created_at = date; }

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

@push('scripts')
    @if(isset($kid))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript">
        var ctxBar = document.getElementById('barChart').getContext('2d');

        var labels = @json(
            $checklists->map(function ($checklist) {
                return ['Checklist #' . $checklist->id, $checklist->created_at->format('d/m/Y')];
            }));

        var data = @json($checklists->pluck('developmentPercentage'));

        var barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labels.map(l => l[0] + '\n' + l[1]),
                datasets: [{
                    label: 'Média Geral do Desenvolvimento (%)',
                    data: data,
                    backgroundColor: data.map(value => {
                        if (value < 30) return 'rgba(220, 53, 69, 0.6)';
                        if (value < 70) return 'rgba(255, 193, 7, 0.6)';
                        return 'rgba(40, 167, 69, 0.6)';
                    }),
                    borderWidth: 1,
                    type: 'bar'
                }, {
                    label: 'Linha de Desenvolvimento',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: false,
                    type: 'line',
                    tension: 0.3
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMin: 0,
                        suggestedMax: 100,
                        title: { display: true, text: 'Percentual de Desenvolvimento (%)' },
                        ticks: { stepSize: 10 }
                    },
                    x: {
                        title: { display: true, text: 'Avaliações por Data' },
                        ticks: { maxRotation: 45, minRotation: 45 }
                    }
                },
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.type === 'bar') return `Desenvolvimento: ${context.raw.toFixed(1)}%`;
                                return context.dataset.label + ': ' + context.raw.toFixed(1) + '%';
                            }
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });

        @php
            $statusData = [0, 0, 0, 0];
            if ($checklists->isNotEmpty()) {
                $lastChecklist = $checklists->first();
                $statusData = [
                    $lastChecklist->competences->where('pivot.note', 0)->count(),
                    $lastChecklist->competences->where('pivot.note', 1)->count(),
                    $lastChecklist->competences->where('pivot.note', 2)->count(),
                    $lastChecklist->competences->where('pivot.note', 3)->count(),
                ];
            }
        @endphp

        var statusData = @json($statusData);
        var ctxStatus = document.getElementById('statusChart').getContext('2d');

        var statusChart = new Chart(ctxStatus, {
            type: 'bar',
            data: {
                labels: ['Não observado', 'Em desenvolvimento', 'Não desenvolvido', 'Desenvolvido'],
                datasets: [{
                    label: 'Distribuição de Competências por Status',
                    data: statusData,
                    backgroundColor: [
                        'rgba(108, 117, 125, 0.2)',
                        'rgba(255, 193, 7, 0.6)',
                        'rgba(220, 53, 69, 0.6)',
                        'rgba(40, 167, 69, 0.6)'
                    ],
                    borderColor: [
                        'rgba(108, 117, 125, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(40, 167, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Quantidade de Competências' } },
                    x: { title: { display: true, text: 'Status de Avaliação' } }
                },
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Status de Desenvolvimento - Última Avaliação', font: { size: 16 } },
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
    @endif
@endpush
