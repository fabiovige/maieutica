@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('checklists.index') }}">Checklist</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gerenciar</li>
        </ol>
    </nav>
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

    <div class="row">
        @foreach($checklist->getStatusAvaliation($checklist->id) as $status)
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $status->note_description }}</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $status->total_competences }}</h6>
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
