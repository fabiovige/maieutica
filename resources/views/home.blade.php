@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@push('styles')
<style>
    .stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.10) !important;
    }
    .stat-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .chart-card {
        border-radius: 12px;
    }
    .ranking-item {
        border-radius: 8px;
        transition: background 0.15s;
    }
    .ranking-item:hover { background: #f8fafc; }
    .rank-badge {
        width: 28px;
        height: 28px;
        min-width: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
    }
</style>
@endpush

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
        </ol>
    </nav>
@endsection

@section('content')

    {{-- Dashboard para Pacientes --}}
    @if(isset($isPatient) && $isPatient)
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 h-100 shadow-sm stat-card" style="background:#e8f0fe;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#4285f4;">
                            <i class="bi bi-file-medical-fill text-white"></i>
                        </div>
                        <div>
                            <div class="small text-muted">Meus Prontuários</div>
                            <div class="fs-4 fw-bold">{{ $totalMedicalRecords }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-8">
                <div class="card border-0 h-100 shadow-sm stat-card">
                    <div class="card-body p-4">
                        <h3 class="mb-2"><i class="bi bi-person-circle text-primary"></i> Bem-vindo(a), {{ auth()->user()->name }}!</h3>
                        <p class="text-muted mb-3">Este é seu espaço para acompanhar suas consultas e evolução do tratamento.</p>
                        <a href="{{ route('medical-records.index') }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Ver Todos os Prontuários
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($latestMedicalRecords->isNotEmpty())
            <div class="card shadow-sm border-0" style="border-radius:12px;">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Últimos Registros</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data da Consulta</th>
                                    <th>Profissional</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestMedicalRecords as $record)
                                    <tr>
                                        <td><i class="bi bi-calendar3 text-muted me-1"></i>{{ $record->session_date_formatted ?? 'N/D' }}</td>
                                        <td><i class="bi bi-person-badge text-primary me-1"></i>{{ $record->creator->name ?? 'N/D' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('medical-records.pdf', $record) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-file-pdf"></i> Ver PDF
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info border-0 shadow-sm" style="border-radius:12px;">
                <i class="bi bi-info-circle"></i> Você ainda não possui prontuários registrados.
            </div>
        @endif

    @else
    {{-- Dashboard Admin / Profissional / Responsável --}}

        {{-- ── Stat Cards ── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100" style="background:#e8f0fe;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#4285f4;">
                            <i class="bi bi-people-fill text-white"></i>
                        </div>
                        <div>
                            <div class="small" style="color:#5f6368;">Crianças</div>
                            <div class="fs-4 fw-bold" style="color:#202124;">{{ $totalChildren }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100" style="background:#f3e8fe;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#7c3aed;">
                            <i class="bi bi-person-fill text-white"></i>
                        </div>
                        <div>
                            <div class="small" style="color:#5f6368;">Adultos</div>
                            <div class="fs-4 fw-bold" style="color:#202124;">{{ $totalAdults }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100" style="background:#e6f4ea;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#34a853;">
                            <i class="bi bi-clipboard2-check-fill text-white"></i>
                        </div>
                        <div>
                            <div class="small" style="color:#5f6368;">Checklists</div>
                            <div class="fs-4 fw-bold" style="color:#202124;">{{ $totalChecklists }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100" style="background:#fef7e0;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#f9ab00;">
                            <i class="bi bi-hourglass-split text-white"></i>
                        </div>
                        <div>
                            <div class="small" style="color:#5f6368;">Em Andamento</div>
                            <div class="fs-4 fw-bold" style="color:#202124;">{{ $checklistsEmAndamento }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm stat-card h-100" style="background:#f0fdf4;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#059669;">
                            <i class="bi bi-graph-up-arrow text-white"></i>
                        </div>
                        <div>
                            <div class="small" style="color:#5f6368;">Média Geral</div>
                            <div class="fs-4 fw-bold" style="color:#202124;">{{ $avgDevelopment }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Gráficos ── --}}
        <div class="row g-3">

            {{-- Gráfico de linha: evolução mensal --}}
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm chart-card h-100">
                    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up text-primary"></i>
                        <span class="fw-semibold">Evolução Média de Desenvolvimento (últimos 6 meses)</span>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart" height="240"></canvas>
                    </div>
                </div>
            </div>

            {{-- Ranking Top 5 --}}
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm chart-card h-100">
                    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-trophy text-warning"></i>
                        <span class="fw-semibold">Top 5 Crianças Mais Evoluídas</span>
                    </div>
                    <div class="card-body py-3">
                        @forelse($top5Kids as $index => $kid)
                            @php
                                $rankColors = ['#f9ab00','#94a3b8','#cd7f32','#64748b','#64748b'];
                                $pct = $kid->progress ?? 0;
                            @endphp
                            <div class="ranking-item d-flex align-items-center gap-3 px-2 py-2 mb-1">
                                <div class="rank-badge text-white" style="background:{{ $rankColors[$index] }};">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold small">{{ $kid->name }}</span>
                                        <span class="small fw-bold" style="color:{{ get_progress_color($pct) }};">{{ $pct }}%</span>
                                    </div>
                                    <div class="progress" style="height:6px;border-radius:4px;">
                                        <div class="progress-bar"
                                             style="width:{{ $pct }}%; background-color:{{ get_progress_color($pct) }} !important;"
                                             role="progressbar"
                                             aria-valuenow="{{ $pct }}"
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-clipboard2-x fs-3 d-block mb-2"></i>
                                Nenhum dado de avaliação encontrado.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    @endif

@endsection

@push('scripts')
@unless(isset($isPatient) && $isPatient)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels  = @json($monthlyTrend->pluck('label'));
    const data    = @json($monthlyTrend->pluck('avg_pct'));
    const counts  = @json($monthlyTrend->pluck('checklist_count'));

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Média de Desenvolvimento (%)',
                data: data,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.08)',
                borderWidth: 2.5,
                pointBackgroundColor: '#3b82f6',
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.4,
                spanGaps: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` Desenvolvimento: ${ctx.raw !== null ? ctx.raw + '%' : 'sem dados'}`,
                        afterLabel: (ctx) => {
                            const c = counts[ctx.dataIndex];
                            return c > 0 ? ` ${c} checklist(s) avaliado(s)` : '';
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: { callback: v => v + '%', stepSize: 20 },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endunless
@endpush
