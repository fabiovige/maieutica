@extends('layouts.app')

@section('title')
    Relatório Psicológico - Modelo 6
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('documentos.index') }}">Documentos</a></li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-file-earmark-text"></i> Modelo 6
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
                    <form action="{{ route('documentos.modelo6') }}" method="POST" target="_blank">
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
                                <label for="solicitante" class="form-label">Solicitante</label>
                                <input type="text" name="solicitante" id="solicitante" class="form-control" placeholder="Ex: Escola Municipal João Silva">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="finalidade" class="form-label">Finalidade</label>
                                <input type="text" name="finalidade" id="finalidade" class="form-control" placeholder="Ex: Avaliação psicológica para fins escolares">
                            </div>
                        </div>

                        <!-- 2. DESCRIÇÃO DA DEMANDA -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-card-text me-2"></i>2. Descrição da Demanda
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="descricao_demanda" class="form-label">Descrição da Demanda <span class="text-danger">*</span></label>
                                <textarea name="descricao_demanda" id="descricao_demanda" class="form-control" rows="5" placeholder="Descrever as informações que recebeu..." required></textarea>
                                <small class="text-muted">Será inserido no texto: "O Sr(a) [nome] procurou atendimento junto ao serviço de psicologia nesta Clínica, onde relatou que [SEU TEXTO AQUI]"</small>
                            </div>
                        </div>

                        <!-- 3. PROCEDIMENTOS -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-clipboard-check me-2"></i>3. Procedimentos
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="numero_encontros" class="form-label">Número de Encontros</label>
                                <input type="number" name="numero_encontros" id="numero_encontros" class="form-control" placeholder="Ex: 8">
                            </div>
                            <div class="col-md-3">
                                <label for="duracao_horas" class="form-label">Duração (horas)</label>
                                <input type="text" name="duracao_horas" id="duracao_horas" class="form-control" placeholder="Ex: 1,5">
                            </div>
                            <div class="col-md-6">
                                <label for="procedimentos_texto" class="form-label">Recursos Técnicos Utilizados <span class="text-danger">*</span></label>
                                <textarea name="procedimentos_texto" id="procedimentos_texto" class="form-control" rows="3" placeholder="Apresentar os recursos técnicos científicos utilizados..." required></textarea>
                                <small class="text-muted">Será inserido após: "Foram realizadas entrevistas e aplicação de testes psicológicos em [X] encontros de [X] horas..."</small>
                            </div>
                        </div>

                        <!-- 4. ANÁLISE -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-graph-up me-2"></i>4. Análise
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="analise" class="form-label">Análise <span class="text-danger">*</span></label>
                                <textarea name="analise" id="analise" class="form-control" rows="6" placeholder="Descrever as principais características e evolução do trabalho realizado..." required></textarea>
                                <small class="text-muted">Será inserido no texto: "Nas primeiras sessões o examinado demonstrou [SEU TEXTO AQUI]"</small>
                            </div>
                        </div>

                        <!-- 5. CONCLUSÃO -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-check-circle me-2"></i>5. Conclusão
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="conclusao" class="form-label">Conclusão <span class="text-danger">*</span></label>
                                <textarea name="conclusao" id="conclusao" class="form-control" rows="5" placeholder="Especificar se houve encaminhamento, orientação e sugestão de continuidade do atendimento..." required></textarea>
                                <small class="text-muted">Será inserido no texto: "Através dos dados analisados foram verificados indícios de [SEU TEXTO AQUI]"</small>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Gerar Relatório PDF
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
