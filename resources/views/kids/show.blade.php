@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gerenciar</li>
        </ol>
    </nav>
@endsection

@section('button')
    <x-button href="{{route('kids.index')}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 ">
            <h5>{{ $kid->name }}. Nascido(a) em: {{ $kid->birth_date }}</h5>
        </div>
    </div>

    <div class="row" id="app">
        <div class="col-md-12 ">
            <div class="card">
                <div class="card-body">

                    @if($checklists->count())
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab-checklist" role="tablist">
                            <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Gráficos</button>
                            <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Checklist</button>
                            <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">Plano</button>
                        </div>
                    </nav>
                    <div class="tab-content" id="nav-tabContent-checklist">
                        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                            <Charts :checklists="{{ $checklists }}"></Charts>
                        </div>
                        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                            <Checklists :checklists="{{ $checklists }}" :checklist_id="{{ $checklist_id }}"></Checklists>
                        </div>
                        <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                            <Planes :checklists="{{ $checklists }}" :checklist_id="{{ $checklist_id }}"></Planes>
                        </div>
                    </div>

                    @endif
                </div>

            </div>

        </div>

    </div>

    @include('includes.information-register', ['data' => $kid, 'action'=>'kids.destroy'])

@endsection

@push('scripts')
    <script type="text/javascript">

    </script>
@endpush
