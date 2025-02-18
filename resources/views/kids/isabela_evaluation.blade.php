<!-- resources/views/competence/isabela_evaluation.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Avaliação de Isabela</h1>
    <p><strong>Criança:</strong> {{ $kid->name }}</p>
    <p><strong>Status Geral:</strong> {{ $statusGeral }}</p>

    <!-- Gráfico de Radar -->
    <canvas id="radarChart" width="400" height="200"></canvas>

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
            @foreach($results as $result)
            <tr>
                <td>{{ $result['competence'] }}</td>
                <td>
                    @if($result['note'] === 1)
                        N (Difícil de obter)
                    @elseif($result['note'] === 2)
                        P (Parcial)
                    @elseif($result['note'] === 3)
                        A (Consistente)
                    @elseif($result['note'] === 0)
                        X (Não Observado)
                    @else
                        Não Avaliada
                    @endif
                </td>
                <td>{{ $result['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Dados para o Gráfico de Radar
    var ctxRadar = document.getElementById('radarChart').getContext('2d');
    var radarLabels = @json(array_column($radarData, 'domain'));
    var radarData = @json(array_column($radarData, 'average'));

    var radarChart = new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: radarLabels,
            datasets: [{
                label: 'Desempenho da Isabela',
                data: radarData,
                backgroundColor: 'rgba(255, 99, 132, 0.2)', // Cor do preenchimento
                borderColor: 'rgba(255, 99, 132, 1)', // Cor da linha
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                r: {
                    suggestedMin: 1,
                    suggestedMax: 3,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            if (value === 1) return 'Não desenvolvido';
                            if (value === 2) return 'Em desenvolvimento';
                            if (value === 3) return 'Desenvolvido';
                            return value;
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
