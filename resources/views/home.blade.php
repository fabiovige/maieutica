@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
        </ol>
    </nav>
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Dashboard</h5>
                    </div>
                    <div class="card-body">
                        Bem-vindo ao sistema!
                    </div>
                </div>
            </div>
        </div>

@endsection
