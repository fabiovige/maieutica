@extends('tutorial.layouts.tutorial')

@section('tutorial-content')
    <!-- Header -->

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-file-check me-2"></i>Checklists
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0">Aprenda como gerenciar checklists</p>
                            <button type="button" class="btn btn-primary" onclick="openVideoModal('Gerenciamento de Checklists', '7KLduXWrcCM')">
                                <i class="bi bi-play-circle me-2"></i>Assistir Vídeo
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0">Aprenda como realizar uma avaliação completa</p>
                            <button type="button" class="btn btn-primary" onclick="openVideoModal('Como Realizar uma Avaliação', '34NT_LEZdtI')">
                                <i class="bi bi-play-circle me-2"></i>Assistir Vídeo
                            </button>
                        </div>
                    </div>
                </div>
            </div>


    <!-- Modal Dinâmico para Vídeos -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel">
                        <i class="bi bi-play-circle me-2"></i>Tutorial
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="ratio ratio-16x9">
                        <iframe 
                            id="videoFrame" 
                            src="" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
// Função global para abrir modal de vídeo dinamicamente
function openVideoModal(title, videoId) {
    const modal = document.getElementById('videoModal');
    const modalTitle = document.getElementById('videoModalLabel');
    const videoFrame = document.getElementById('videoFrame');
    
    // Atualiza o título do modal
    modalTitle.innerHTML = '<i class="bi bi-play-circle me-2"></i>' + title;
    
    // Configura a URL do vídeo
    const videoUrl = `https://www.youtube.com/embed/${videoId}?hd=1&vq=hd1080&quality=hd1080`;
    videoFrame.src = videoUrl;
    
    // Abre o modal
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const videoModal = document.getElementById('videoModal');
    const videoFrame = document.getElementById('videoFrame');
    
    // Limpa o iframe quando o modal é fechado
    videoModal.addEventListener('hide.bs.modal', function () {
        videoFrame.src = '';
    });
});
</script>
@endpush 