@extends('layouts.app')

@section('content')

    <h2>Plano Automático para {{ $kid->name }}</h2>

    <div class="card mt-4">
        <div class="card-header">
            Distribuição das Competências
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
            </div>

            <div class="row">
                @foreach ($statusAvaliation as $status)
                    <div class="col-md-3">
                        <div title="Visualizar Competências" class="card mt-2 {{ ($status->note === 0 ? 'bg-secondary' :
                                                ($status->note === 2 ? 'bg-danger' :
                                                ($status->note === 1 ? 'bg-warning' :
                                                'bg-primary'))) }} text-{{ $status->note === 1 ? 'dark' : 'white' }}"
                             onclick="showCompetences({{ $checklist->id }}, {{ $status->note }})">
                            <div class="card-body cursor-pointer">
                                <h5 class="card-title" id="competencesTitle2">{{ $notesDescription[$status->note] }}</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3>{{ $status->total_competences }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tabela para exibir as competências -->
            <div class="table-responsive mt-4" id="competencesTable" style="display: none;">
                <div class="d-flex justify-content-between">
                    <h3 id="competencesTitle">Competências para o Status: </h3>
                    <div>
                        <button class="btn btn-secondary me-2" onclick="toggleView()">
                            <i class="bi bi-arrow-left-right"></i> Alternar Visualização
                        </button>
                        <a id="generatePlanBtn" href="{{ route('kids.pdfplaneauto', ['id' => $kid->id, 'checklistId' => $checklist->id, 'note' => $status->note]) }}" class="btn btn-primary" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Gerar Plano
                        </a>
                    </div>
                </div>

                <!-- Container para iframe -->
                <div id="pdfViewer" class="mt-3" style="display: none;">
                    <iframe id="pdfFrame" style="width: 100%; height: 600px; border: none;"></iframe>
                </div>

                <!-- Container para tabela -->
                <div id="tableViewer" class="mt-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Domínio</th>
                                <th>Nível</th>
                                <th>Competência</th>
                            </tr>
                        </thead>
                        <tbody id="competencesContent">
                            <!-- Conteúdo será carregado via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de status
var ctxStatus = document.getElementById('statusChart').getContext('2d');

// Preparar dados dos status
var statusLabels = @json(array_values($notesDescription));
var statusData = @json($statusAvaliation->pluck('total_competences'));
var statusColors = [
    'rgba(108, 117, 125, 0.6)',  // Bootstrap secondary
    'rgba(255, 193, 7, 0.6)',    // Bootstrap warning
    'rgba(220, 53, 69, 0.6)',    // Bootstrap danger
    'rgba(0, 123, 255, 1)'     // Bootstrap primary
];
var statusBorders = [
    'rgba(108, 117, 125, 1)',    // Bootstrap secondary
    'rgba(255, 193, 7, 1)',      // Bootstrap warning
    'rgba(220, 53, 69, 1)',      // Bootstrap danger
    'rgba(57, 255, 20, 1)'       // Bootstrap primary
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

function showCompetences(checklistId, note) {
    $('#competencesTable').show();

    // Atualizar o href do botão e src do iframe
    const baseUrl = "{{ route('kids.pdfplaneauto', ['id' => $kid->id, 'checklistId' => $checklist->id, 'note' => ':note']) }}";
    const newUrl = baseUrl.replace(':note', note);
    $('#generatePlanBtn').attr('href', newUrl);
    $('#pdfFrame').attr('src', newUrl);

    // Mostrar loading na tabela
    $('#competencesContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Carregando...</span></div></div>');

    // Fazer a requisição AJAX
    $.ajax({
        url: `/api/checklists/${checklistId}/competences/${note}`,
        method: 'GET',
        success: function(response) {
            let html = '';
            response.forEach(function(item) {
                html += `<tr>
                    <td>${item.id}</td>
                    <td>${item.domain_name}</td>
                    <td>${item.level_id}</td>
                    <td>${item.description}</td>
                </tr>`;
            });
            $('#competencesContent').html(html);
            $('#competencesTitle').text(`Competências para o Status: ${note === 1 ? 'Mais ou menos' : note === 2 ? 'Difícil de obter' : note === 3 ? 'Consistente' : 'Não observado'}`);
        },
        error: function(xhr) {
            $('#competencesContent').html('<div class="alert alert-danger">Erro ao carregar as competências.</div>');
        }
    });
}

function toggleView() {
    const tableView = $('#tableViewer');
    const pdfView = $('#pdfViewer');

    if (tableView.is(':visible')) {
        tableView.hide();
        pdfView.show();
    } else {
        pdfView.hide();
        tableView.show();
    }
}
</script>
@endpush
