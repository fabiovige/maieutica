<!-- resources/views/competence/evaluation_all_domains.blade.php -->
<!-- Barra quente-frio para o status geral -->
<h3>Status Geral: {{ $statusGeral }}</h3>
<div class="progress mb-4" style="height: 30px;">
    <div class="progress-bar bg-danger" role="progressbar"
        style="width: {{ $statusGeral == 'Atrasado' ? '100%' : '0%' }};">
        Não desenvolvido
    </div>
    <div class="progress-bar bg-warning" role="progressbar"
        style="width: {{ $statusGeral == 'Mais ou menos' ? '100%' : '0%' }};">
        Em desenvolvimento
    </div>
    <div class="progress-bar bg-success" role="progressbar"
        style="width: {{ $statusGeral == 'Adiantado' ? '100%' : '0%' }};">
        Desenvolvido
    </div>
</div>
