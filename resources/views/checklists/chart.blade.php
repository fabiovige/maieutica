@extends('layouts.app')

@section('title')
    Gráficos de Desenvolvimento
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('checklists.index') }}">Checklist</a></li>
            <li class="breadcrumb-item active" aria-current="page">Esfera</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <x-kid-info-card :kid="$checklist->kid" />
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div id="app">
                        <Charts :checklist-id="{{$checklist->id}}" :checklists="{{$checklists->toJson()}}"></Charts>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            @include('includes.information-register', ['data' => $checklist, 'can' => 'remove checklists', 'action' => 'checklists.destroy'])
        </div>
    </div>
@endsection


@push ('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

    <script type="text/javascript">
        $('#checklist_id').change(function(){
            let checklist_id = $(this).val();
            window.location.href = "{{URL::to('checklists')}}/" + checklist_id + "/chart"
        });
    </script>

@endpush
