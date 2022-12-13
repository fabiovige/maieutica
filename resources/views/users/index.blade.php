@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Usuários</li>
        </ol>
    </nav>
@endsection

@section('button')
    @can('users.store')
    <x-button href="{{route('users.create')}}" icon="plus" name="Novo" type="link" class="primary btn-sm"></x-button>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 ">
            <div class="card">
                <div class="card-header">
                    Lista de usuários
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover dt-responsive nowrap dataTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Papel</th>
                                <th>E-mail</th>
                                <th>Acesso liberado</th>
                                <th style="width: 30px"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push ('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push ('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

    <script type="text/javascript">
        $('.dataTable').DataTable({
            language: {
                url: "{{ asset('vendor/datatable/pt-BR.json') }}"
            },
            processing: true,
            serverSide: true,
            autoWidth: true,
            responsive: true,
            ajax: '{{ route("users.index_data") }}',
            columns: [{
                data: 'id',
                name: 'id',
                },
                {
                    data: 'name',
                    name: 'name',
                },
                {
                    data: 'role',
                    name: 'role',
                    searchable: false
                },
                {
                    data: 'email',
                    name: 'email',
                },
                {
                    data: 'allow',
                    name: 'allow',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

    </script>

@endpush
