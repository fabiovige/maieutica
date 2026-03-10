@extends('layouts.app-sidebar')

@section('title', 'Teste Sidebar')

@section('breadcrumb-items')
    <li class="breadcrumb-item active">Teste do Novo Layout</li>
@endsection

@section('actions')
    <a href="{{ route('home.index') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">✅ Novo Layout com Sidebar</h3>
                <p class="card-text text-muted">
                    Layout moderno com sidebar vertical, totalmente responsivo e com recursos de colapsar.
                </p>
                
                <hr>
                
                <h5>🎨 Recursos:</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i> 
                        <strong>Sidebar colapsável:</strong> Clique na seta ◀ no topo do menu para recolher/expandir
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i> 
                        <strong>Menu sanfona:</strong> Clique em "Lixeira" para abrir/fechar submenu
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i> 
                        <strong>Responsivo:</strong> Adapta-se a desktop, tablet e mobile
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i> 
                        <strong>Container fluid:</strong> Usa largura total da tela
                    </li>
                </ul>

                <hr>
                
                <h5>📱 Teste de Responsividade:</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Dispositivo</th>
                                <th>Largura</th>
                                <th>Comportamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="bi bi-display"></i> Desktop</td>
                                <td>&gt; 991px</td>
                                <td>Sidebar fixo, pode colapsar para ícones</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-tablet"></i> Tablet</td>
                                <td>768-991px</td>
                                <td>Sidebar escondido, botão ☰ para abrir</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-phone"></i> Mobile</td>
                                <td>&lt; 768px</td>
                                <td>Sidebar como drawer com overlay</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3">
                    <h6><i class="bi bi-info-circle"></i> Como usar o menu colapsável:</h6>
                    <ol class="mb-0">
                        <li>No <strong>desktop</strong>, clique na seta ◀ ao lado do logo para recolher o menu</li>
                        <li>Apenas os <strong>ícones</strong> ficam visíveis, economizando espaço</li>
                        <li>Clique novamente para <strong>expandir</strong></li>
                        <li>O estado é <strong>salvo automaticamente</strong></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title">🧪 Teste o Menu Lixeira</h5>
                <p class="text-muted small">Clique em "Lixeira" no menu lateral para testar o submenu sanfona.</p>
                
                <hr>
                
                <h6>Atalhos:</h6>
                <ul class="small text-muted">
                    <li><kbd>☰</kbd> Abrir sidebar (mobile)</li>
                    <li><kbd>◀</kbd> Colapsar/Expandir (desktop)</li>
                    <li><kbd>Lixeira</kbd> Abrir submenu</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
