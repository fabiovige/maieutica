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

@section('content')
    <div class="row">
        <div class="col-md-12 ">
            <h5>{{ $kid->name }} - {{ $kid->FullNameMonths }}</h5>
        </div>
    </div>

    <div class="row" id="app">
        <div class="col-md-12 ">
            <div class="card">
                <div class="card-body">

                    @if ($checklists->count())
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab-checklist" role="tablist">
                                <button class="nav-link active" id="nav-profile-tab" data-bs-toggle="tab"
                                    data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile"
                                    aria-selected="true">Perfil</button>
                                <button class="nav-link" id="nav-charts-tab" data-bs-toggle="tab"
                                    data-bs-target="#nav-charts" type="button" role="tab" aria-controls="nav-charts"
                                    aria-selected="false">Gráficos</button>
                                <button class="nav-link" id="nav-checklist-tab" data-bs-toggle="tab"
                                    data-bs-target="#nav-checklist" type="button" role="tab"
                                    aria-controls="nav-checklist" aria-selected="false">Checklist</button>
                                <button class="nav-link" id="nav-plane-tab" data-bs-toggle="tab" data-bs-target="#nav-plane"
                                    type="button" role="tab" aria-controls="nav-plane"
                                    aria-selected="false">Plano</button>
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent-checklist">
                            <div class="tab-pane fade show active" id="nav-profile" role="tabpanel"
                                aria-labelledby="nav-profile-tab">

                                {{-- <Dashboard user="{{ auth()->user()->id }}"></Dashboard> --}}
                                {{-- <Resume :responsible="{{ $kid->responsible()->first() }}" :kid="{{ $kid }}">
                                </Resume> --}}
                                <Resume :responsible="{{ $kid->responsible()->first() }}"
                                    :kid="{{ $kid }}"
                                    :checklist="{{ $countChecklists }}"
                                    :plane="{{ $countPlanes }}"
                                >
                                </Resume>
                            </div>
                            <div class="tab-pane fade show" id="nav-charts" role="tabpanel"
                                aria-labelledby="nav-charts-tab">
                                <Charts :checklists="{{ $checklists }}"></Charts>
                            </div>
                            <div class="tab-pane fade" id="nav-checklist" role="tabpanel"
                                aria-labelledby="nav-checklist-tab">
                                <Checklists :checklists="{{ $checklists }}" :checklist_id="{{ $checklist_id }}">
                                </Checklists>
                            </div>
                            <div class="tab-pane fade" id="nav-plane" role="tabpanel" aria-labelledby="nav-plane-tab">
                                <Planes
                                    :checklists="{{ $checklists }}"
                                    :checklist_id="{{ $checklist_id }}"
                                    :kid_id="{{ $kid->id }}"
                                    :can-create-plane="{{ Auth::user()->can('create planes') }}"
                                    :can-view-plane="{{ Auth::user()->can('view planes') }}"
                                ></Planes>
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
