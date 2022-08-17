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

@section('button')
    <x-button href="{{route('checklists.index')}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 ">
            <div class="card">
                <div class="card-header ">
                    {{ __('visualizar') }}
                </div>
                <div class="card-body">

                    <div id="app">
                        <Competences></Competences>
                    </div>


{{--                    <div class="form-group">--}}
{{--                        <div class="row">--}}

{{--                            <div class="col-md-2">--}}
{{--                                <label for="level">Nível</label> <br>--}}
{{--                                <select class="form-select" aria-label="level" name="level">--}}
{{--                                    @foreach($levels as $key => $value)--}}
{{--                                        <option value="{{ $key }}" > {{ $value }} </option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}

{{--                            <div class="col-md-4">--}}
{{--                                <label for="competence_id">Competências</label> <br>--}}
{{--                                <select class="form-select" aria-label="competence_id" name="competence_id">--}}
{{--                                    @foreach($competences as $competence)--}}
{{--                                        <option value="{{ $competence->id }}" > {{ $competence->name }} </option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

                </div>

                <div class="card-footer  d-flex justify-content-between">

                </div>
            </div>
        </div>
    </div>
@endsection

