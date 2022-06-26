@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Papéis</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.show', $role->id) }}">Gerenciar</a></li>
            <li class="breadcrumb-item active" aria-current="page">Recursos</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3>Editar recursos do papél: <strong>{{$role->name}}</strong></h3>
            <a href="{{route('roles.show', $role->id)}}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar </a>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">

            <form action="{{ route('roles.resources.update', $role->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header">
                        <div class="h5">{{ $role->role }}</div>
                    </div>
                    <div class="card-body">

                        <div class="custom-control custom-checkbox">

                            <div class="form-check">
                                <input class="form-check-input permission-input"
                                       type="checkbox"
                                       id="checkAll"
                                    {{ (count($resources) == count($role->resources()->pluck('id')->toArray())) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="checkAll">
                                    Todos
                                </label>
                            </div>

                        </div>
                    @foreach($resources as $resource)

                                    <div class="custom-control custom-checkbox">

                                        <div class="form-check">
                                            <input class="form-check-input permission-input"
                                                   type="checkbox"
                                                   name="abilities[]"
                                                   id="customCheck{{$resource->id}}"
                                                   value="{{$resource->id}}"
                                                   @if($role->resources->contains($resource)) checked @endif
                                            >
                                            <label class="form-check-label" for="customCheck{{$resource->id}}">
                                                {{ $resource->name }} ({{ $resource->ability }})
                                            </label>
                                        </div>

                                    </div>

                            @endforeach

                    </div>

                    <div class="card-footer">

                        <button class="btn btn-success" type="submit">
                            <i class="bi bi-check-circle"></i> Atualizar</button>

                    </div>
                </div>
            </form>
        </div>
        @include('includes.information-register', ['data' => $role])
    </div>
@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script>
        $("#checkAll").click(function(){
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    </script>
@endpush
