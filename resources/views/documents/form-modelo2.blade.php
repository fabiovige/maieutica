@extends('layouts.app')

@section('title')
    Declaração - Modelo 2
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('documentos.index') }}">Documentos</a></li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-file-earmark-check"></i> Modelo 2
    </li>
@endsection

@section('actions')
    <a href="{{ route('documentos.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left me-2"></i>Voltar
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <!-- Formulário -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('documentos.modelo2') }}" method="POST" target="_blank">
                        @csrf

                        <!-- Informações do Paciente -->
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-person-badge me-2"></i>Informações do Paciente
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="kid_id" class="form-label">Paciente <span class="text-danger">*</span></label>
                                <select name="kid_id" id="kid_id" class="form-select" required>
                                    <option value="">Selecione uma criança</option>
                                    @foreach($kids as $kid)
                                        <option value="{{ $kid->id }}">{{ $kid->name }} - {{ $kid->age }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">A data de início do acompanhamento será calculada automaticamente com base no cadastro do paciente</small>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Gerar Declaração PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informação -->
            <div class="alert alert-info mt-3" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Atenção:</strong> O PDF será aberto em uma nova aba do navegador. Certifique-se de desbloquear pop-ups se necessário.
            </div>
        </div>
    </div>
</div>
@endsection
