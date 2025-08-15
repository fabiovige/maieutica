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
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0">Aprenda como clonar uma checklist</p>
                            <button type="button" class="btn btn-primary" onclick="openVideoModal('Como Realizar uma Avaliação', 'Nz472Chbwrs')">
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
(function() {
    'use strict';
    
    window.openVideoModal = function(title, videoId) {
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap não disponível');
            return;
        }
        
        var modal = document.getElementById('videoModal');
        var modalTitle = document.getElementById('videoModalLabel');
        var videoFrame = document.getElementById('videoFrame');
        
        if (!modal || !modalTitle || !videoFrame) {
            console.error('Elementos não encontrados');
            return;
        }
        
        modalTitle.innerHTML = '<i class="bi bi-play-circle me-2"></i>' + title;
        videoFrame.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=0&rel=0';
        
        var bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    };
    
    document.addEventListener('DOMContentLoaded', function() {
        var videoModal = document.getElementById('videoModal');
        var videoFrame = document.getElementById('videoFrame');
        
        if (videoModal && videoFrame) {
            videoModal.addEventListener('hide.bs.modal', function() {
                videoFrame.src = '';
            });
        }
    });
})();
</script>
@endpush 