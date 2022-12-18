@extends('layouts.app')

@section('breadcrumb')

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Responsáveis</li>
            </ol>
        </nav>

@endsection

@section('button')
    @can('responsibles.store')
    <x-button href="{{route('responsibles.create')}}" icon="plus" name="Novo" type="link" class="primary btn-sm"></x-button>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 ">
            <div class="card">
                <div class="card-header">
                    Lista de Responsáveis
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover dt-responsive nowrap dataTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Celular</th>
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
    <link rel="stylesheet" href="{{ asset('vendor/datatable/responsive.bootstrap5.min.css') }}">
@endpush

@push ('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/datatable/dataTables.responsive.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/datatable/responsive.bootstrap5.js') }}"></script>

    <script type="text/javascript">
        $('.dataTable').DataTable({
            language: {
                url: "{{ asset('vendor/datatable/pt-BR.json') }}"
            },
            processing: true,
            serverSide: true,
            autoWidth: true,
            responsive: true,
            ajax: '{{ route("responsibles.index_data") }}',
            columns: [{
                data: 'id',
                name: 'id',
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'cell',
                    name: 'cell',
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

