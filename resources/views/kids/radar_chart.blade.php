<!-- resources/views/evaluation/radar_chart.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Avaliação Geral - Nível {{ $levelId }}</h1>
    <p><strong>Criança:</strong> {{ $kid->name }}</p>
    <p><strong>Idade:</strong> {{ $ageInMonths }} meses</p>

    <!-- Gráfico de Radar Geral por Domínios -->
    <canvas id="radarChart" width="400" height="400"></canvas>

    <!-- Lista de Domínios -->
    <h2>Detalhes por Domínio</h2>
    <ul>
        @foreach($domains as $domain)
            <li>
                <a href="{{ route('kids.domainDetails', ['kidId' => $kid->id, 'levelId' => $levelId, 'domainId' => $domain->id]) }}">
                    {{ $domain->name }} ({{ $domain->initial }})
                </a>
            </li>
        @endforeach
    </ul>
</div>

<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Dados para o Gráfico de Radar
    var ctxRadar = document.getElementById('radarChart').getContext('2d');
    var radarLabels = @json(array_column($radarDataDomains, 'domain'));
    var radarData = @json(array_column($radarDataDomains, 'average'));

    var radarChart = new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: radarLabels,
            datasets: [{
                label: 'Desempenho por Domínio',
                data: radarData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)', // Cor do preenchimento
                borderColor: 'rgba(54, 162, 235, 1)', // Cor da linha
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
