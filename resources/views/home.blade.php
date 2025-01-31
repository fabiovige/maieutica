@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row mb-2 justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div id="app">
                        <div class="row">

                            @foreach ($kids as $kid)
                                <div class="col-12 col-md-6 col-lg-4 col-xl-4">
                                    <Resumekid :user="{{ $kid->first() }}"
                                        :kid="{{ $kid }}"
                                        :checklist="{{ $countChecklists[$kid->id] }}"
                                        :plane="{{ $countPlanes[$kid->id] }}"
                                    >
                                    </Resumekid>
                                </div>
                            @endforeach

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
