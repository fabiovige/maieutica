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
@endsection

@section('actions')
    @can('create checklists')
        @if ($kid)
            <button onclick="openDateModal()" class="btn btn-primary">
                <span class="d-flex align-items-center">
                    <i class="bi bi-plus-lg me-2"></i> Novo Checklist
                </span>
            </button>

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
                let selectedType = 'atual';
                let selectedDate = null;

                function openDateModal() {
                    $('#dateModal').modal('show');
                }

                document.querySelectorAll('input[name="checklistType"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        selectedType = this.value;
                        const retroGroup = document.getElementById('retroactiveDateGroup');
                        if (this.value === 'retro') {
                            retroGroup.style.display = 'block';
                        } else {
                            retroGroup.style.display = 'none';
                        }
                    });
                });

                document.getElementById('retroactiveDate').addEventListener('change', function() {
                    selectedDate = this.value;
                });

                document.getElementById('confirmDateBtn').addEventListener('click', function() {
                    let url = '{{ route("checklists.store") }}';
                    let data = {
                        kid_id: {{ $kid->id }},
                        level: {{ $kid->level ?? 4 }},
                        _token: '{{ csrf_token() }}'
                    };

                    if (selectedType === 'retro' && selectedDate) {
                        data.created_at = selectedDate;
                    }

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $('#dateModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao criar checklist');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao criar checklist: ' + error.message);
                    });
                }
            </script>
        @endif
    @endcan
@endsection

@section('content')

<x-data-filter
    :filters="[
        [
            'type' => 'text',
            'name' => 'search',
            'placeholder' => 'Digite o nome da criança ou descrição do checklist...',
            'value' => $filters['search'] ?? '',
            'class' => 'col-md-6'
        ],
        [
            'type' => 'select',
            'name' => 'situation',
            'options' => [
                '' => 'Todas as situações',
                'a' => 'Aberto',
                'f' => 'Fechado'
            ],
            'value' => $filters['situation'] ?? '',
            'class' => 'col-md-2'
        ],
        [
            'type' => 'select',
            'name' => 'level',
            'options' => [
                '' => 'Todos os níveis',
                '1' => 'Nível 1',
                '2' => 'Nível 2',
                '3' => 'Nível 3',
                '4' => 'Nível 4'
            ],
            'value' => $filters['level'] ?? '',
            'class' => 'col-md-2'
        ]
    ]"
    action-route="checklists.index"
    :hidden-fields="$kid ? ['kidId' => $kid->id] : []"
    :total-results="isset($checklists) ? $checklists->total() : 0"
    entity-name="checklist"
/>

<div class="row mb-4">
    @if (isset($kid))
        <div class="col-md-12">
            <x-kid-info-card :kid="$kid" />
        </div>
    @endif
</div>

<div class="row">
    <div class="{{ isset($kid) ? 'col-md-6' : 'col-md-12' }}">
        <x-data-list
            :data="$checklists"
            :columns="[
                ['label' => '#', 'attribute' => 'id', 'class' => 'text-center'],
                ...(isset($kid) ? [] : [['label' => 'Criança', 'attribute' => 'kid.name']]),
                [
                    'label' => 'Situação', 
                    'callback' => fn($item) => '<span class=\"badge ' . ($item->situation == 'a' ? 'bg-success' : 'bg-secondary') . '\">' . ($item->situation == 'a' ? 'Aberto' : 'Fechado') . '</span>'
                ],
                ['label' => 'Data', 'attribute' => 'created_at'],
                [
                    'label' => 'Progresso', 
                    'callback' => function($item) {
                        $percentage = $item->developmentPercentage ?? 0;
                        $color = function_exists('get_progress_color') ? get_progress_color($percentage) : '#007bff';
                        return '
                            <div class=\"progress\" role=\"progressbar\" aria-label=\"checklist' . $item->id . '\" 
                                 aria-valuenow=\"' . $percentage . '\" aria-valuemin=\"0\" aria-valuemax=\"100\">
                                <div class=\"progress-bar\" style=\"width: ' . $percentage . '%; background-color: ' . $color . ' !important\"></div>
                            </div>
                            ' . $percentage . '%';
                    }
                ]
            ]"
            :actions="[
                [
                    'type' => 'edit',
                    'route' => 'checklists.edit',
                    'permission' => 'edit checklists',
                    'condition_callback' => fn($item) => $item->situation == 'a' || auth()->user()->can('admin'),
                    'url_callback' => fn($item) => isset($kid) ? route('checklists.edit', ['checklist' => $item->id, 'kidId' => $kid->id]) : route('checklists.edit', $item->id)
                ],
                [
                    'type' => 'custom',
                    'label' => 'Avaliação',
                    'icon' => 'bi-check2-square',
                    'class' => 'btn-outline-primary',
                    'route' => 'checklists.fill',
                    'permission' => 'avaliation checklist',
                    'condition_callback' => fn($item) => $item->situation == 'a' || auth()->user()->can('admin')
                ],
                [
                    'type' => 'clone',
                    'route' => 'checklists.clonar',
                    'permission' => 'clone checklists',
                    'condition_callback' => fn($item) => $item->situation == 'a' || auth()->user()->can('admin'),
                    'url_callback' => fn($item) => route('checklists.clonar', $item->id) . '?kid_id=' . $item->kid_id
                ],
                [
                    'type' => 'delete',
                    'route' => 'checklists.destroy',
                    'permission' => 'delete checklists'
                ]
            ]"
            empty-message="Nenhum checklist cadastrado."
            empty-with-filters-message="Nenhum checklist encontrado com os filtros aplicados."
            :has-filters-applied="!empty($filters['search']) || !empty($filters['situation']) || !empty($filters['level'])"
            :clear-filters-url="route('checklists.index', $kid ? ['kidId' => $kid->id] : [])"
        />
        
        @if(isset($checklists) && method_exists($checklists, 'links'))
            <x-data-pagination :paginator="$checklists" :default-per-page="$defaultPerPage" />
        @endif
    </div>
    
    @if (isset($kid))
        <div class="col-md-6 mt-2">
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
    @if(isset($kid) && isset($checklists) && $checklists->isNotEmpty())
        <script type="text/javascript">
            var ctxBar = document.getElementById('barChart').getContext('2d');

            // Formatar as datas dos checklists para labels
            var labels = @json(
                $checklists->map(function ($checklist) {
                    return ['#' . $checklist->id, $checklist->created_at->format('d/m/Y')];
                })->toArray()
            );

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
                                minRotation: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Evolução do Desenvolvimento - {{ $kid->name }}',
                            font: {
                                size: 16
                            }
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    var index = context[0].dataIndex;
                                    return labels[index][0] + ' - ' + labels[index][1];
                                },
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Gráfico de Status (último checklist)
            var ctxStatus = document.getElementById('statusChart').getContext('2d');
            var lastChecklist = @json($checklists->first());

            // Dados fictícios para status do desenvolvimento (você precisará implementar a lógica real)
            var statusData = [15, 25, 35, 25]; // Exemplo: Não observado, Em desenvolvimento, Emergente, Consistente
            var statusLabels = ['Não observado', 'Em desenvolvimento', 'Emergente', 'Consistente'];

            var statusChart = new Chart(ctxStatus, {
                type: 'bar',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        label: 'Competências',
                        data: statusData,
                        backgroundColor: [
                            'rgba(220, 53, 69, 0.7)',   // Vermelho
                            'rgba(255, 193, 7, 0.7)',   // Amarelo
                            'rgba(13, 202, 240, 0.7)',  // Azul
                            'rgba(40, 167, 69, 0.7)'    // Verde
                        ],
                        borderColor: [
                            'rgba(220, 53, 69, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(13, 202, 240, 1)',
                            'rgba(40, 167, 69, 1)'
                        ],
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
    @endif
@endpush