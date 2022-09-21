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
            <h5>
                {{ $checklist->kid->name }}, Nascido(a) em: {{ $checklist->kid->birth_date }}.
                Checklist: {{ $checklist->id }}, Criado em: {{ $checklist->created_at->format('d/m/Y') }}
            </h5>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">

                            <label for="level">Checklists</label>
                            <select name="checklist_id" id="checklist_id" class="form-select" >
                                @foreach($checklist->kid->checklists()->get() as $c => $v)
                                    <option value="{{ $v->id }}" @if($checklist->id == $v->id) selected @endif > {{ $v->created_at->format('d/m/Y') }} Cod. {{ $v->id }} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-9">
                            Nível: {{ $checklist->level }} <br>
                            Situação: {{ \App\Models\Checklist::SITUATION[$checklist->situation] }} <br>
                            Descrição: {{ $checklist->description }} <br>
                        </div>
                    </div>
                </div>
                <div class="card-footer  d-flex justify-content-between">
                    @can('checklists.update')
                        <x-button href="{{route('checklists.fill', $checklist->id)}}" icon="check-circle" name="Editar" type="link" class="dark"></x-button>
                    @endcan

                    @can('checklists.destroy')
                        <form action="{{ route('checklists.destroy', $checklist->id) }}" name="form-delete" method="post">
                            @csrf
                            @method('DELETE')
                            <x-button icon="trash" name="Enviar para lixeira" type="submit" class="danger  form-delete"></x-button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="card mt-2">
                <div class="card-body">
                    <div id="app">
                        <Checklists checklist_id="{{ $checklist->id }}" level="{{ $checklist->level }}"></Checklists>
                    </div>
                </div>
            </div>

        </div>
        @include('includes.information-register', ['data' => $checklist])
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
