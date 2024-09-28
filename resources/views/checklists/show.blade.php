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
