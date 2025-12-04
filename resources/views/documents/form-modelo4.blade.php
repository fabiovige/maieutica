@extends('layouts.app')

@section('title')
    Parecer Psicológico - Modelo 4
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('documentos.index') }}">Documentos</a></li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-file-earmark-ruled"></i> Modelo 4
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
                    <form action="{{ route('documentos.modelo4') }}" method="POST" target="_blank">
                        @csrf

                        <!-- 1. IDENTIFICAÇÃO -->
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-person-badge me-2"></i>1. Identificação
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="kid_id" class="form-label">Paciente <span class="text-danger">*</span></label>
                                <select name="kid_id" id="kid_id" class="form-select" required>
                                    <option value="">Selecione uma criança</option>
                                    @foreach($kids as $kid)
                                        <option value="{{ $kid->id }}">{{ $kid->name }} - {{ $kid->age }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="solicitante" class="form-label">Solicitante <span class="text-danger">*</span></label>
                                <input type="text" name="solicitante" id="solicitante" class="form-control" placeholder="Ex: Tribunal de Justiça de SP" required>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="finalidade" class="form-label">Finalidade <span class="text-danger">*</span></label>
                                <input type="text" name="finalidade" id="finalidade" class="form-control" placeholder="Ex: Processo judicial nº 1234/2024" required>
                            </div>
                            <div class="col-md-6">
                                <label for="professionals" class="form-label">Profissionais Envolvidos</label>
                                <select name="professionals[]" id="professionals" class="form-select" multiple size="3">
                                    @foreach($professionals as $professional)
                                        <option value="{{ $professional['id'] }}">{{ $professional['name'] }} - CRP {{ $professional['crp'] }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Mantenha CTRL pressionado para selecionar múltiplos profissionais</small>
                            </div>
                        </div>

                        <!-- 2. DESCRIÇÃO DA DEMANDA -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-card-text me-2"></i>2. Descrição da Demanda
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="descricao_demanda" class="form-label">Descrição da Demanda <span class="text-danger">*</span></label>
                                <textarea name="descricao_demanda" id="descricao_demanda" class="form-control" rows="5" placeholder="Descrever as informações, motivos e razões que produziram o pedido de parecer..." required></textarea>
                                <small class="text-muted">Explique o contexto e a motivação para a solicitação do parecer psicológico</small>
                            </div>
                        </div>

                        <!-- 3. ANÁLISE -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-graph-up me-2"></i>3. Análise
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="analise" class="form-label">Análise <span class="text-danger">*</span></label>
                                <textarea name="analise" id="analise" class="form-control" rows="6" placeholder="Analisar a questão explanada e argumentar com base nos fundamentos éticos, técnicos e conceituais da psicologia..." required></textarea>
                                <small class="text-muted">Apresente a análise técnica fundamentada em teorias e conceitos da psicologia</small>
                            </div>
                        </div>

                        <!-- 4. CONCLUSÃO -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-check-circle me-2"></i>4. Conclusão
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="conclusao" class="form-label">Conclusão <span class="text-danger">*</span></label>
                                <textarea name="conclusao" id="conclusao" class="form-control" rows="5" placeholder="Apresentar posicionamento sobre a questão-problema ou documentos psicológicos questionados..." required></textarea>
                                <small class="text-muted">Conclusão clara e objetiva sobre o parecer solicitado</small>
                            </div>
                        </div>

                        <!-- 5. REFERÊNCIAS -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-book me-2"></i>5. Referências <span class="text-danger">*</span>
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="referencias" class="form-label">Referências Bibliográficas</label>
                                <textarea name="referencias" id="referencias" class="form-control" rows="4" placeholder="Digite as referências bibliográficas utilizadas (uma por linha)..." required></textarea>
                                <small class="text-muted">Ex: AMERICAN PSYCHIATRIC ASSOCIATION. DSM-5: Manual diagnóstico e estatístico de transtornos mentais. 5. ed. Porto Alegre: Artmed, 2014.</small>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Gerar Parecer PDF
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
