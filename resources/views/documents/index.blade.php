@extends('layouts.app')

@section('title')
    Geração de Documentos
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-file-earmark-text"></i> Documentos
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <!-- Cards dos Modelos -->
            <div class="row">
                <!-- Modelo 1 -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Declaração - Modelo 1</h5>
                                    <small class="text-muted">Declaração completa</small>
                                </div>
                            </div>
                            <p class="card-text text-muted flex-grow-1">
                                Declaração de acompanhamento psicológico com informações sobre dias, horários e previsão de término.
                            </p>
                            <div class="d-grid">
                                <a href="{{ route('documentos.modelo1.form') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Gerar Documento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modelo 2 -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-file-earmark-check text-success" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Declaração - Modelo 2</h5>
                                    <small class="text-muted">Declaração simplificada</small>
                                </div>
                            </div>
                            <p class="card-text text-muted flex-grow-1">
                                Declaração simples de acompanhamento psicológico com data de início do tratamento.
                            </p>
                            <div class="d-grid">
                                <a href="{{ route('documentos.modelo2.form') }}" class="btn btn-success">
                                    <i class="bi bi-plus-circle me-2"></i>Gerar Documento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modelo 3 -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 border-warning">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-file-earmark-medical text-warning" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Laudo Psicológico - Modelo 3</h5>
                                    <small class="text-muted">Laudo completo</small>
                                </div>
                            </div>
                            <p class="card-text text-muted flex-grow-1">
                                Laudo psicológico completo com identificação, descrição da demanda, procedimentos, análise, conclusão e referências.
                            </p>
                            <div class="d-grid">
                                <a href="{{ route('documentos.modelo3.form') }}" class="btn btn-warning">
                                    <i class="bi bi-plus-circle me-2"></i>Gerar Laudo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modelo 4 -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 border-info">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-file-earmark-ruled text-info" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Parecer Psicológico - Modelo 4</h5>
                                    <small class="text-muted">Parecer técnico</small>
                                </div>
                            </div>
                            <p class="card-text text-muted flex-grow-1">
                                Parecer psicológico com identificação, descrição da demanda, análise técnica, conclusão e referências bibliográficas.
                            </p>
                            <div class="d-grid">
                                <a href="{{ route('documentos.modelo4.form') }}" class="btn btn-info">
                                    <i class="bi bi-plus-circle me-2"></i>Gerar Parecer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modelo 5 -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 border-danger">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-danger bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-file-earmark-person text-danger" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Relatório Multiprofissional - Modelo 5</h5>
                                    <small class="text-muted">Equipe multiprofissional</small>
                                </div>
                            </div>
                            <p class="card-text text-muted flex-grow-1">
                                Relatório multiprofissional com análise separada por profissional, procedimentos, conclusão conjunta e declaração de sigilo.
                            </p>
                            <div class="d-grid">
                                <a href="{{ route('documentos.modelo5.form') }}" class="btn btn-danger">
                                    <i class="bi bi-plus-circle me-2"></i>Gerar Relatório
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modelo 6 -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 border-dark">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-dark bg-opacity-10 rounded p-3 me-3">
                                    <i class="bi bi-file-earmark-text text-dark" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Relatório Psicológico - Modelo 6</h5>
                                    <small class="text-muted">Relatório individual</small>
                                </div>
                            </div>
                            <p class="card-text text-muted flex-grow-1">
                                Relatório psicológico individual com descrição da demanda, procedimentos, análise detalhada e conclusão com encaminhamentos.
                            </p>
                            <div class="d-grid">
                                <a href="{{ route('documentos.modelo6.form') }}" class="btn btn-dark">
                                    <i class="bi bi-plus-circle me-2"></i>Gerar Relatório
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="alert alert-info mt-4" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Informação:</strong> Todos os documentos serão gerados em formato PDF com o padrão visual da clínica (logo, marca d'água, footer).
            </div>
        </div>
    </div>
</div>
@endsection
