<!-- resources/views/competence/evaluation_all_domains.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Avaliação - Resultados por Domínio</h1>
    <p><strong>Criança:</strong> {{ $kid->name }}</p>
    <p><strong>Idade:</strong> {{ $kid->age_in_months }} meses</p>
    <p><strong>Número do Checklist:</strong> {{ $checklist->id }}</p>

    <!-- Barra quente-frio para o status geral -->
    <h3>Status Geral: {{ $statusGeral }}</h3>
    <div class="progress mb-4" style="height: 30px;">
        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $statusGeral == 'Atrasado' ? '100%' : '0%' }};">
            Atrasado
        </div>
        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $statusGeral == 'Em processo' ? '100%' : '0%' }};">
            Em processo
        </div>
        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $statusGeral == 'Adiantado' ? '100%' : '0%' }};">
            Adiantado
        </div>
    </div>

    @foreach($domainResults as $domain)
        <h2>{{ $domain['domain'] }}</h2>

        <!-- Gráfico de Barras -->
        <canvas id="barChart{{ $loop->index }}" width="400" height="200"></canvas>

        <!-- Gráfico de Pizza -->
        <canvas id="pieChart{{ $loop->index }}" width="400" height="200"></canvas>

        <!-- Lista de Competências e Status -->
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Competência</th>
                    <th>Nota</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($domain['competences'] as $competence)
                <tr>
                    <td>{{ $competence['competence'] }}</td>
                    <td>
                        @if($competence['note'] === 1)
                            N (Incapaz)
                        @elseif($competence['note'] === 2)
                            P (Parcial)
                        @elseif($competence['note'] === 3)
                            A (Adquirido)
                        @elseif($competence['note'] === 0)
                            X (Não Observado)
                        @else
                            Não Avaliada
                        @endif
                    </td>
                    <td>{{ $competence['status'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>
    @endforeach
</div>

<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @foreach($domainResults as $domain)
        // Dados para o Gráfico de Barras
        var ctxBar{{ $loop->index }} = document.getElementById('barChart{{ $loop->index }}').getContext('2d');
        var barChart{{ $loop->index }} = new Chart(ctxBar{{ $loop->index }}, {
            type: 'bar',
            data: {
                labels: @json(array_column($domain['competences'], 'competence')),
                datasets: [{
                    label: 'Notas',
                    data: @json(array_column($domain['competences'], 'note')),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)', // Azul
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 3,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                if (value === 1) return 'N (Incapaz)';
                                if (value === 2) return 'P (Parcial)';
                                if (value === 3) return 'A (Adquirido)';
                                return value;
                            }
                        }
                    }
                }
            }
        });

        // Dados para o Gráfico de Pizza
        var statusCounts{{ $loop->index }} = {
            'Adiantada': 0,
            'Dentro do esperado': 0,
            'Atrasada': 0,
            'Incapaz': 0,
            'Não Observado': 0,
            'Não Avaliada': 0
        };

        @foreach($domain['competences'] as $competence)
            statusCounts{{ $loop->index }}['{{ $competence['status'] }}'] = (statusCounts{{ $loop->index }}['{{ $competence['status'] }}'] || 0) + 1;
        @endforeach

        var ctxPie{{ $loop->index }} = document.getElementById('pieChart{{ $loop->index }}').getContext('2d');
        var pieChart{{ $loop->index }} = new Chart(ctxPie{{ $loop->index }}, {
            type: 'pie',
            data: {
                labels: Object.keys(statusCounts{{ $loop->index }}),
                datasets: [{
                    data: Object.values(statusCounts{{ $loop->index }}),
                    backgroundColor: [
                        '#28a745', // Adiantada - Verde
                        '#17a2b8', // Dentro do esperado - Azul claro
                        '#dc3545', // Atrasada - Vermelho
                        '#ffc107', // Incapaz - Amarelo
                        '#6c757d', // Não Observado - Cinza
                        '#343a40', // Não Avaliada - Cinza Escuro
                    ]
                }]
            },
            options: {
                responsive: true,
            }
        });
    @endforeach
</script>
@endsection
