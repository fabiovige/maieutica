@extends('layouts.app')

@push('styles')
<style>
    /* Estilos para cards de status do checklist */
    .card-icon {
        width: 60px;
        height: 60px;
        min-width: 60px;
        font-size: 1.75rem;
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
    <div class="row">
        <div class="col-md-12 ">
            <h5>{{ $checklist->kid->name }} - {{ $checklist->kid->FullNameMonths }}</h5>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">


            <div class="card">
                <div class="card-body">
                    Checklist: {{ $checklist->id }}
                    <div id="app">
                        <Checklists checklist_id="{{ $checklist->id }}" level="{{ $checklist->level }}"></Checklists>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 mt-3">
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

    @include('includes.information-register', [
        'data' => $checklist,
        'action'=>'checklists.destroy',
        'can' => 'remove checklists'
    ])

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
