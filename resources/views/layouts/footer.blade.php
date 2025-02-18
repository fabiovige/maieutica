<footer>
    <div class="footer-wrapper">
        <div class="container-fluid">
            <div class="row py-2 d-flex justify-content-between align-items-center">
                <div class="col-auto">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-heart-fill text-danger me-1"></i>
                        <span class="small mb-0">
                            &copy; {{ now()->format('Y') }} {{ config('app.name') }}
                        </span>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="d-flex align-items-center gap-3">
                        <a href="#" class="text-muted small text-decoration-none">
                            <i class="bi bi-shield-check"></i> Privacidade
                        </a>
                        <a href="#" class="text-muted small text-decoration-none">
                            <i class="bi bi-question-circle"></i> Ajuda
                        </a>
                        <span class="badge bg-primary">
                            <i class="bi bi-code-slash"></i> v{{ config('app.version') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
