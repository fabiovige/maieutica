@extends('layouts.app')

@section('breadcrumb')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checklists</li>
        </ol>
    </nav>

@endsection

@section('button')
    @can('kids.store')
        <x-button href="{{route('checklists.create')}}" icon="plus" name="Cadastrar" type="link" class="dark"></x-button>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 ">
            <div class="card">
                <div class="card-header">
                    Lista de checklists
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover dt-responsive nowrap dataTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Criança</th>
                                <th>Nível</th>
                                <th>Situação</th>
                                <th>Data de criação</th>
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
            ajax: '{{ route("checklists.index_data") }}',
            columns: [{
                data: 'id',
                name: 'id',
                },
                {
                    data: 'kid_id',
                    name: 'kid_id'
                },
                {
                    data: 'level',
                    name: 'level'
                },
                {
                    data: 'situation',
                    name: 'situation'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: false,
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

