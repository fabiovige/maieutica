<!-- Sidebar Menu do Tutorial -->
<div class="card h-100">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>Tutorial
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <!-- Checklists -->
            <a href="{{ route('tutorial.checklists') }}" class="list-group-item list-group-item-action {{ request()->routeIs('tutorial.checklists') ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="bi bi-file-check me-3"></i>
                    <div>
                        <h6 class="mb-0">Checklists</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div> 