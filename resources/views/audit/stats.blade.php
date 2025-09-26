@extends('layouts.app')

@section('title', 'Estatísticas de Auditoria LGPD')

@section('breadcrumb-items')
<li class="breadcrumb-item"><a href="{{ route('audit.index') }}">Auditoria LGPD</a></li>
<li class="breadcrumb-item active">Estatísticas</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ number_format($totalLogs) }}</h4>
                            <p class="card-text">Total de Logs</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ number_format($recentLogs) }}</h4>
                            <p class="card-text">Logs dos Últimos 7 Dias</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos e Estatísticas -->
    <div class="row">
        <!-- Ações mais Frequentes -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ações mais Frequentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ação</th>
                                    <th class="text-end">Quantidade</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($actionStats as $stat)
                                <tr>
                                    <td>
                                        <span class="badge
                                            @switch($stat->action)
                                                @case('CREATE') bg-success @break
                                                @case('UPDATE') bg-warning @break
                                                @case('DELETE') bg-danger @break
                                                @case('read') bg-info @break
                                                @default bg-secondary
                                            @endswitch
                                        ">
                                            {{ $stat->action }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($stat->count) }}</td>
                                    <td class="text-end">{{ number_format(($stat->count / $totalLogs) * 100, 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recursos mais Acessados -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recursos mais Acessados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Recurso</th>
                                    <th class="text-end">Quantidade</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resourceStats as $stat)
                                <tr>
                                    <td>{{ $stat->resource }}</td>
                                    <td class="text-end">{{ number_format($stat->count) }}</td>
                                    <td class="text-end">{{ number_format(($stat->count / $totalLogs) * 100, 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Usuários mais Ativos -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Usuários mais Ativos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th class="text-end">Atividades</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userStats as $stat)
                                <tr>
                                    <td>{{ $stat->user->name ?? 'Usuário removido' }}</td>
                                    <td class="text-end">{{ number_format($stat->count) }}</td>
                                    <td class="text-end">{{ number_format(($stat->count / $totalLogs) * 100, 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Atividade Diária -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Atividade dos Últimos 30 Dias</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyActivityChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <a href="{{ route('audit.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> Ver Todos os Logs
                    </a>
                    <a href="{{ route('audit.export') }}" class="btn btn-success">
                        <i class="fas fa-download"></i> Exportar Dados
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Atividade Diária
    const ctx = document.getElementById('dailyActivityChart').getContext('2d');
    const dailyData = @json($dailyActivity);

    const labels = dailyData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    });

    const data = dailyData.map(item => item.count);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Atividades por Dia',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush