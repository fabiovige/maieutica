@extends('layouts.app')

@section('title')
    Laudo Psicológico - Modelo 3
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('documentos.index') }}">Documentos</a></li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-file-earmark-medical"></i> Modelo 3
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
                    <form action="{{ route('documentos.modelo3') }}" method="POST" target="_blank">
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
                                <input type="text" name="solicitante" id="solicitante" class="form-control" placeholder="Ex: Escola XYZ">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="finalidade" class="form-label">Finalidade</label>
                                <input type="text" name="finalidade" id="finalidade" class="form-control" value="Avaliação psicológica" placeholder="Ex: Avaliação para fins escolares">
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
                            <div class="col-md-6">
                                <label for="nome_informante" class="form-label">Nome do Informante</label>
                                <input type="text" name="nome_informante" id="nome_informante" class="form-control" placeholder="Ex: Sr. João Silva (pai)">
                            </div>
                            <div class="col-md-6">
                                <label for="hipotese_diagnostico" class="form-label">Hipótese Diagnóstica</label>
                                <input type="text" name="hipotese_diagnostico" id="hipotese_diagnostico" class="form-control" placeholder="Ex: Transtorno de Ansiedade">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="sintomas" class="form-label">Sintomas Relatados</label>
                                <textarea name="sintomas" id="sintomas" class="form-control" rows="3" placeholder="Descreva os sintomas observados e relatados..."></textarea>
                                <small class="text-muted">Ex: ansiedade, dificuldade de concentração, fobias específicas</small>
                            </div>
                            <div class="col-md-6">
                                <label for="consequencias" class="form-label">Consequências nas Relações Sociais e Trabalho</label>
                                <textarea name="consequencias" id="consequencias" class="form-control" rows="3" placeholder="Descreva as consequências observadas..."></textarea>
                                <small class="text-muted">Ex: isolamento social, baixo desempenho escolar</small>
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
                                <label for="procedimentos_texto" class="form-label">Recursos Técnicos Utilizados</label>
                                <textarea name="procedimentos_texto" id="procedimentos_texto" class="form-control" rows="2" placeholder="Descreva os recursos técnicos científicos utilizados..."></textarea>
                            </div>
                        </div>

                        <!-- 4. ANÁLISE -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-graph-up me-2"></i>4. Análise
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="analise_texto" class="form-label">Análise das Sessões</label>
                                <textarea name="analise_texto" id="analise_texto" class="form-control" rows="4" placeholder="Descreva as principais características e evolução do trabalho realizado..."></textarea>
                                <small class="text-muted">As informações devem ser sustentadas em fatos e teorias respeitando a fundamentação teórica e o instrumental técnico utilizado.</small>
                            </div>
                        </div>

                        <!-- 5. CONCLUSÃO -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-check-circle me-2"></i>5. Conclusão
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="diagnostico" class="form-label">Diagnóstico</label>
                                <input type="text" name="diagnostico" id="diagnostico" class="form-control" placeholder="Ex: Ansiedade">
                            </div>
                            <div class="col-md-4">
                                <label for="sintoma_principal" class="form-label">Sintoma Principal</label>
                                <input type="text" name="sintoma_principal" id="sintoma_principal" class="form-control" placeholder="Ex: Fobia social">
                            </div>
                            <div class="col-md-4">
                                <label for="cid" class="form-label">CID</label>
                                <input type="text" name="cid" id="cid" class="form-control" placeholder="Ex: F41.0">
                            </div>
                        </div>

                        <!-- 6. REFERÊNCIAS (Opcional) -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-book me-2"></i>6. Referências (Opcional)
                        </h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="referencias" class="form-label">Referências Bibliográficas</label>
                                <textarea name="referencias" id="referencias" class="form-control" rows="3" placeholder="Digite as referências bibliográficas utilizadas (uma por linha)..."></textarea>
                                <small class="text-muted">Ex: AMERICAN PSYCHIATRIC ASSOCIATION. DSM-5: Manual diagnóstico e estatístico de transtornos mentais. 5. ed. Porto Alegre: Artmed, 2014.</small>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Gerar Laudo PDF
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
