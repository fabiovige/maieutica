@extends('tutorial.layouts.tutorial')

@section('tutorial-content')
    <!-- Header -->

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-file-check me-2"></i>Gerenciamento de Checklists
                    </h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="mb-0">Aprenda como gerenciar checklists no sistema Maiêutica</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#videoModal">
                            <i class="bi bi-play-circle me-2"></i>Assistir Vídeo Tutorial
                        </button>
                    </div>
                </div>
            </div>


    <!-- Modal do Vídeo -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel">
                        <i class="bi bi-play-circle me-2"></i>Tutorial - Gerenciamento de Checklists
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
document.addEventListener('DOMContentLoaded', function() {
    const videoModal = document.getElementById('videoModal');
    const videoFrame = document.getElementById('videoFrame');
    const videoUrl = 'https://www.youtube.com/embed/7KLduXWrcCM?hd=1&vq=hd1080&quality=hd1080';
    
    videoModal.addEventListener('show.bs.modal', function () {
        videoFrame.src = videoUrl;
    });
    
    videoModal.addEventListener('hide.bs.modal', function () {
        videoFrame.src = '';
    });
});
</script>
@endpush 