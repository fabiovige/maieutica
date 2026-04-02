@extends('layouts.app')

@push('styles')
<style>
    /* Estilos para cards de status do checklist */
    .card-icon {
        width: 60px;
        height: 60px;
        min-width: 60px;
        font-size: var(--fs-3xl);
        flex-shrink: 0;
    }

    /* Cores customizadas para status */
    .status-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endpush

@section('title')
    Visualizar Checklist
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('checklists.index') }}">Checklists</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('kids.show', $checklist->kid->id) }}">{{ $checklist->kid->name }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        Checklist #{{ $checklist->id }}
    </li>
@endsection

@section('content')
    @php
        $isOpen  = $checklist->situation_label === 'Aberto';
        $isAdmin = auth()->check() && auth()->user()->can('checklist-edit-all');
        $kidId   = $checklist->kid->id ?? null;
    @endphp

    {{-- Header do checklist --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
        <div class="card-body px-4 py-3">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <div class="flex-grow-1">
                    @if($checklist->kid)
                        <a href="{{ route('kids.show', $checklist->kid->id) }}" class="text-decoration-none fw-semibold fs-5 text-dark">
                            {{ $checklist->kid->name }}
                        </a>
                        <span class="text-muted small ms-2">{{ $checklist->kid->FullNameMonths }}</span>
                    @endif
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="badge {{ $isOpen ? 'bg-success' : 'bg-secondary opacity-75' }}">
                            <i class="bi {{ $isOpen ? 'bi-unlock' : 'bi-lock' }}"></i>
                            {{ $checklist->situation_label }}
                        </span>
                        <span class="badge bg-primary-subtle text-primary-emphasis">
                            <i class="bi bi-layers"></i> Nível {{ $checklist->level }}
                        </span>
                        <span class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i>{{ $checklist->created_at->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ $kidId ? route('checklists.index', ['kidId' => $kidId]) : route('checklists.index') }}"
                       class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        {{-- Barra de ações --}}
        @canany(['checklist-edit', 'checklist-edit-all', 'checklist-avaliation', 'checklist-plane-manual', 'checklist-plane-automatic', 'checklist-clone'])
        <div class="card-footer bg-transparent border-top px-4 py-2 d-flex flex-wrap gap-2">
            @can('checklist-edit')
                <a href="{{ $kidId ? route('checklists.edit', ['checklist' => $checklist->id, 'kidId' => $kidId]) : route('checklists.edit', $checklist->id) }}"
                   class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            @endcan
            @if($isOpen || $isAdmin)
                @can('checklist-avaliation')
                    <a href="{{ route('checklists.fill', $checklist->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-clipboard2-check"></i> Avaliação
                    </a>
                @endcan
                @if($kidId)
                    @can('checklist-plane-manual')
                        <a href="{{ route('kids.showPlane', $kidId) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-journal-text"></i> Plano Manual
                        </a>
                    @endcan
                    @can('checklist-plane-automatic')
                        <a href="{{ route('kid.plane-automatic', ['kidId' => $kidId, 'checklistId' => $checklist->id]) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-magic"></i> Plano Auto
                        </a>
                    @endcan
                @endif
                @can('checklist-clone')
                    <a href="{{ route('checklists.clonar', ['id' => $checklist->id, 'kid_id' => $checklist->kid_id]) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-copy"></i> Clonar
                    </a>
                @endcan
            @endif
        </div>
        @endcanany
    </div>

    {{-- Cards de status --}}
    <div class="row mb-4">
        @foreach($checklist->getStatusAvaliation($checklist->id) as $status)
            @php
                $statusColors = [
                    0 => ['bg' => 'secondary', 'text' => 'Não observado'],
                    1 => ['bg' => 'warning', 'text' => 'Em desenvolvimento'],
                    2 => ['bg' => 'danger', 'text' => 'Não desenvolvido'],
                    3 => ['bg' => 'success', 'text' => 'Desenvolvido']
                ];
                $color = $statusColors[$status->note]['bg'] ?? 'secondary';
            @endphp
            <div class="col-md-3 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">{{ $status->note_description }}</h6>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-{{ $color }}">
                                <i class="bi bi-clipboard-check text-white"></i>
                            </div>
                            <div class="ps-3">
                                <h4 class="mb-0 fw-bold">{{ $status->total_competences }}</h4>
                                <span class="text-muted small">competências</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Competências (Vue component) --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
        <div class="card-body">
            <div id="app">
                <Checklists checklist_id="{{ $checklist->id }}" level="{{ $checklist->level }}"></Checklists>
            </div>
        </div>
    </div>

@endsection


@push ('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

    <script type="text/javascript">
        $('#checklist_id').change(function(){
            let checklist_id = $(this).val();
            window.location.href = "{{URL::to('checklists')}}/" + checklist_id
        });
    </script>

@endpush
