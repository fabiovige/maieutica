@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Crianças</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row mb-2">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3>Crianças</h3>
            @can('kids.store')
                <a href="{{route('kids.create')}}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Cadastrar </a>
            @endcan
        </div>
    </div>
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped table-bordered table-hover table-responsive dataTable">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th  scope="col">Nome</th>
                    <th  scope="col">Data de nascimento</th>
                    <th  scope="col"></th>
                </tr>
                </thead>
            </table>
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
            ajax: '{{ route("kids.index_data") }}',
            columns: [{
                data: 'id',
                name: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'birth_date',
                    name: 'birth_date'
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
