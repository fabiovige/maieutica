@extends('layouts.app')

@section('title', 'Tutorial - Sistema Maiêutica')

@section('breadcrumb-items')
<li class="breadcrumb-item">
    <a href="{{ route('tutorial.index') }}">
        <i class="bi bi-book"></i> Tutorial
    </a>
</li>
@if(isset($breadcrumb_title))
<li class="breadcrumb-item active" aria-current="page">
    {{ $breadcrumb_title }}
</li>
@endif
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Menu -->
        <div class="col-lg-4 col-xl-3">
            @include('tutorial.partials.menu')
        </div>

        <!-- Conteúdo Principal -->
        <div class="col-lg-8 col-xl-9">
            @yield('tutorial-content')
        </div>
    </div>
</div>

@push('styles')
<style>
/* Responsividade do sidebar */
@media (max-width: 991.98px) {
    .col-lg-4 {
        margin-bottom: 1rem;
    }
}
</style>
@endpush
@endsection 