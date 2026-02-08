{{-- 
    GUIA DE BOTÕES PADRONIZADOS - MAIÊUTICA
    
    Este arquivo serve como referência visual e de código para o uso
    correto dos botões padronizados no sistema.
    
    Copie os exemplos abaixo para manter consistência em todo o sistema.
--}}

@extends('layouts.app')

@section('title', 'Guia de Botões')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Sistema de Botões Padronizados</h2>
            
            {{-- BOTÕES PRIMÁRIOS --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Botões por Hierarquia</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-muted mb-3">Primário (Ação principal)</h6>
                    <div class="mb-4">
                        <button class="btn btn-primary">Salvar</button>
                        <button class="btn btn-primary"><i class="bi bi-save"></i> Salvar</button>
                        <button class="btn btn-primary" disabled>Desabilitado</button>
                        <code class="d-block mt-2">btn-primary</code>
                    </div>
                    
                    <h6 class="text-muted mb-3">Secundário (Cancelar/Voltar)</h6>
                    <div class="mb-4">
                        <button class="btn btn-secondary">Cancelar</button>
                        <button class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</button>
                        <code class="d-block mt-2">btn-secondary</code>
                    </div>
                    
                    <h6 class="text-muted mb-3">Sucesso (Confirmar/Ativar)</h6>
                    <div class="mb-4">
                        <button class="btn btn-success">Confirmar</button>
                        <button class="btn btn-success"><i class="bi bi-check-circle"></i> Ativar</button>
                        <code class="d-block mt-2">btn-success</code>
                    </div>
                    
                    <h6 class="text-muted mb-3">Perigo (Excluir/Desativar)</h6>
                    <div class="mb-4">
                        <button class="btn btn-danger">Excluir</button>
                        <button class="btn btn-danger"><i class="bi bi-trash"></i> Excluir</button>
                        <code class="d-block mt-2">btn-danger</code>
                    </div>
                    
                    <h6 class="text-muted mb-3">Aviso (Editar/Modificar)</h6>
                    <div class="mb-4">
                        <button class="btn btn-warning">Editar</button>
                        <button class="btn btn-warning"><i class="bi bi-pencil"></i> Editar</button>
                        <code class="d-block mt-2">btn-warning</code>
                    </div>
                    
                    <h6 class="text-muted mb-3">Info (Visualizar/Detalhes)</h6>
                    <div class="mb-4">
                        <button class="btn btn-info">Visualizar</button>
                        <button class="btn btn-info"><i class="bi bi-eye"></i> Ver</button>
                        <code class="d-block mt-2">btn-info</code>
                    </div>
                </div>
            </div>
            
            {{-- BOTÕES OUTLINE --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Botões Outline (Ações secundárias)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Use outline para ações menos importantes ou múltiplas opções lado a lado.</p>
                    
                    <div class="mb-3">
                        <button class="btn btn-outline-primary">Primário</button>
                        <button class="btn btn-outline-secondary">Secundário</button>
                        <button class="btn btn-outline-success">Sucesso</button>
                        <button class="btn btn-outline-danger">Perigo</button>
                        <button class="btn btn-outline-warning">Aviso</button>
                        <button class="btn btn-outline-info">Info</button>
                    </div>
                    
                    <h6 class="text-muted mb-3 mt-4">Exemplo: Ações em tabela</h6>
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i> Ver</a>
                            <a href="#" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil"></i> Editar</a>
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Excluir</button>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- TAMANHOS --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tamanhos de Botões</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-primary btn-sm">Pequeno (btn-sm)</button>
                        <button class="btn btn-primary">Padrão</button>
                        <button class="btn btn-primary btn-lg">Grande (btn-lg)</button>
                    </div>
                    <p class="text-muted small">
                        <strong>btn-sm:</strong> Use em tabelas, listas e ações compactas<br>
                        <strong>Padrão:</strong> Formulários e ações principais<br>
                        <strong>btn-lg:</strong> CTAs importantes em páginas de destino
                    </p>
                </div>
            </div>
            
            {{-- EXEMPLOS DE FORMULÁRIO --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Exemplo: Formulário</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control" placeholder="Digite o nome">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="Digite o email">
                            </div>
                        </div>
                        
                        {{-- BOTÕES DE FORMULÁRIO --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            {{-- EXEMPLOS DE LISTAGEM --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Exemplo: Listagem/Ações</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Status</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>João Silva</td>
                                    <td><span class="badge bg-success">Ativo</span></td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="#" class="btn btn-outline-info btn-sm" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline-warning btn-sm" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-outline-danger btn-sm" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- REGRAS DE USO --}}
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">📋 Regras de Uso</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>Sempre use ícones</strong> junto com o texto quando possível (melhora UX)</li>
                        <li><strong>btn-sm em tabelas</strong> - mantém as linhas compactas</li>
                        <li><strong>Outline para ações secundárias</strong> - menos destaque visual</li>
                        <li><strong>Desabilite (disabled)</strong> botões quando a ação não estiver disponível</li>
                        <li><strong>Máximo 3-4 botões</strong> visíveis por linha em tabelas</li>
                        <li><strong>Agrupe botões relacionados</strong> usando <code>btn-group</code></li>
                        <li><strong>Ordem de ações:</strong> Ver → Editar → Excluir (da esquerda para direita)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
