@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Papéis</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar</li>
        </ol>
    </nav>
@endsection

@section('content')

    <div class="row">

        <div class="col-12">

            <form action="{{route('roles.store')}}" method="post">
                @csrf
                @method('POST')

                <div class="card">
                    <div class="card-header">
                        Cadastrar
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label>Nome</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name"
                                   value="{{old('name')}}">

                            @error('name')
                            <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>

                        <label>Pemissões</label>

                        <div class="custom-control custom-checkbox">

                            <div class="form-check">
                                <input class="form-check-input permission-input"
                                       type="checkbox"
                                       id="checkAll"
                                >
                                <label class="form-check-label" for="checkAll">
                                    Todos
                                </label>
                            </div>

                        </div>
                        <div class="row">
                            @foreach($resources as $resource)
                                <div class="col-md-4 py-2">
                                    <div class="card">
                                        <div class="card-header">{{ $resource->name }}</div>
                                        <div class="card-body">
                                            @foreach ( $resource->abilities as $ability )

                                                <div class="custom-control custom-checkbox">
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-input"
                                                            type="checkbox"
                                                            name="abilities[]"
                                                            id="customCheck{{$ability->id}}"
                                                            value="{{$ability->id}}"
                                                            @if($ability->id == old('abilities')) checked @endif
                                                        >
                                                        <label class="form-check-label" for="customCheck{{$ability->id}}">
                                                            {{ $ability->name }}
                                                        </label>
                                                    </div>

                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- <div class="custom-control custom-checkbox">

                                        <div class="form-check">
                                            <input class="form-check-input permission-input"
                                                type="checkbox"
                                                name="abilities[]"
                                                id="customCheck{{$resource->id}}"
                                                value="{{$resource->id}}"
                                                @if($resource->id == old('abilities')) checked @endif
                                            >
                                            <label class="form-check-label" for="customCheck{{$resource->id}}">
                                                {{ $resource->name }} ({{ $resource->ability }})
                                            </label>
                                        </div>

                                    </div> --}}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <x-button icon="check" name="Salvar" type="submit" class="success"></x-button>
                    </div>
                </div>

            </form>

        </div>

    </div>

@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script>
        $("#checkAll").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    </script>
@endpush
