@extends('layouts.app')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('checklists.index',['kidId'=>$kid->id]) }}">Checklists</a></li>
        <li class="breadcrumb-item active" aria-current="page">Planos</li>
    </ol>
</nav>
@endsection




@section('title')
Plano Manual
@endsection

@section('breadcrumb-items')
@if($kid)

<li class="breadcrumb-item">
    <a href="{{ route('kids.index') }}">
        <i class="bi bi-people"></i> Crianças
    </a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('checklists.index', ['kidId' => $kid->id]) }}">
        <i class="bi bi-card-checklist"></i> Checklists
    </a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    <i class="bi bi-card-checklist"></i> Plano Manual
</li>
@else
<li class="breadcrumb-item active" aria-current="page">
    <i class="bi bi-card-checklist"></i> Checklists
</li>

@endif
</li>
@endsection


@section('content')
<div class="row">
    <div class="col-md-12 d-flex justify-content-between">
        <div>
            <h5>{{ $kid->name }} - {{ $kid->FullNameMonths }}</h5>
        </div>
        <div>
            <h5>Checklist: {{ $checklist->id }} - {{ $checklist->created_at->format('d/m/Y')}}</h5>
        </div>
    </div>
</div>

<div class="row" id="app">
    <div class="col-md-12 ">
        <div class="card">
            <div class="card-body">

                @if ($checklists->count())
                <nav>
                    <div class="nav nav-tabs" id="nav-tab-checklist" role="tablist">
                        <button class="nav-link" id="nav-plane-tab" data-bs-toggle="tab" data-bs-target="#nav-plane"
                            type="button" role="tab" aria-controls="nav-plane" aria-selected="false">Plano</button>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent-checklist">
                    <div class="tab-pane fade show active" id="nav-plane" role="tabpanel"
                        aria-labelledby="nav-plane-tab">
                        <Planes :checklists="{{ $checklists }}" :checklist_id="{{ $checklist_id }}"
                            :kid_id="{{ $kid->id }}" :age-in-months="{{ $ageInMonths }}"
                            :can-create-plane="{{ Auth::user()->can('create planes') }}"
                            :can-view-plane="{{ Auth::user()->can('view planes') }}"></Planes>
                    </div>
                </div>
                @endif

            </div>

        </div>

    </div>

</div>

@include('includes.information-register', ['data' => $kid, 'action' => 'kids.destroy', 'can' => 'remove kids'])
@endsection

@push('scripts')
<script type="text/javascript"></script>
@endpush
