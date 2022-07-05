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
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="h2">{{ $kid->name }} </div>
            <div><a href="{{route('kids.index')}}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar </a></div>
        </div>

        <div class="col-12">

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ficha-tab" data-bs-toggle="tab" data-bs-target="#ficha" type="button" role="tab" aria-controls="ficha" aria-selected="true">Ficha</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="esfera-tab" data-bs-toggle="tab" data-bs-target="#esfera" type="button" role="tab" aria-controls="esfera" aria-selected="false">Representação das Esferas</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="calculo-tab" data-bs-toggle="tab" data-bs-target="#calculo" type="button" role="tab" aria-controls="calculo" aria-selected="false">Cálculo de Desenvolvimento</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="checklist-tab" data-bs-toggle="tab" data-bs-target="#checklist" type="button" role="tab" aria-controls="checklist" aria-selected="false">Checklist</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="plano-tab" data-bs-toggle="tab" data-bs-target="#plano" type="button" role="tab" aria-controls="plano" aria-selected="false">Plano</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <!-- ficha -->
                <div class="tab-pane fade show active" id="ficha" role="tabpanel" aria-labelledby="home-tab">
                    <div class="card">
                        <div class="card-body">
                            <div class="h5">{{ $kid->name }}</div>
                            <div class="h6"> {{ $kid->months }} meses - {{ $kid->birth_date }} - Cod. {{ $kid->id }}</div>
                        </div>
                        <div class="card-footer  d-flex justify-content-between">
                            @can('kids.edit')
                                <a href="{{ route('kids.edit', $kid->id) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil-square"></i> Editar</a>
                            @endcan

                            @can('kids.destroy')
                                <form action="{{ route('kids.destroy', $kid->id) }}" name="form-delete" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-warning form-delete">
                                        <i class="bi bi-trash3"></i> Enviar para lixeira</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="esfera" role="tabpanel" aria-labelledby="profile-tab">esfera</div>
                <div class="tab-pane fade" id="calculo" role="tabpanel" aria-labelledby="contact-tab">calculo</div>
                <div class="tab-pane fade" id="checklist" role="tabpanel" aria-labelledby="contact-tab">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div><a href="#" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Editar</a></div>
                                <div>atual</div>
                                <div><a href="{{ route('checklists.createChecklist', $kid) }}" class="btn btn-dark"><i class="bi bi-plus"></i> Novo checklist</a></div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="tab-pane fade" id="plano" role="tabpanel" aria-labelledby="contact-tab">plano</div>
            </div>

        </div>

        @include('includes.information-register', ['data' => $kid])

    </div>
@endsection
