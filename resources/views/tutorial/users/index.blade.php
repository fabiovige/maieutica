@extends('tutorial.layouts.tutorial')

@section('tutorial-content')
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-people me-2"></i>Usuários
                    </h4>
                </div>
                <div class="card-body">
                    <p class="mb-0">Gerencie usuários do sistema: criar contas, definir permissões e controlar acessos.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Tarefas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Gerenciar Usuários</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#criar-usuario" class="list-group-item list-group-item-action">
                            <i class="bi bi-person-plus me-2"></i>Como criar usuário
                        </a>
                        <a href="#editar-usuario" class="list-group-item list-group-item-action">
                            <i class="bi bi-person-gear me-2"></i>Como editar usuário
                        </a>
                        <a href="#ativar-usuario" class="list-group-item list-group-item-action">
                            <i class="bi bi-person-check me-2"></i>Como ativar/desativar usuário
                        </a>
                        <a href="#resetar-senha" class="list-group-item list-group-item-action">
                            <i class="bi bi-key me-2"></i>Como resetar senha
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Permissões e Papéis</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#definir-papel" class="list-group-item list-group-item-action">
                            <i class="bi bi-shield-check me-2"></i>Como definir papel do usuário
                        </a>
                        <a href="#gerenciar-permissoes" class="list-group-item list-group-item-action">
                            <i class="bi bi-gear me-2"></i>Como gerenciar permissões
                        </a>
                        <a href="#criar-papel" class="list-group-item list-group-item-action">
                            <i class="bi bi-plus-circle me-2"></i>Como criar novo papel
                        </a>
                        <a href="#verificar-acesso" class="list-group-item list-group-item-action">
                            <i class="bi bi-eye me-2"></i>Como verificar acessos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seções de Conteúdo -->
    <div class="row mt-4">
        <div class="col-12">
            
            <!-- Criar Usuário -->
            <div class="card mb-4" id="criar-usuario">
                <div class="card-header">
                    <h5 class="mb-0">Como Criar Usuário</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Acesse o menu <strong>Usuários</strong></li>
                        <li>Clique no botão <strong>Novo Usuário</strong></li>
                        <li>Preencha os dados básicos:
                            <ul>
                                <li>Nome completo</li>
                                <li>Email (será o login)</li>
                                <li>Telefone</li>
                                <li>Endereço (use a busca por CEP)</li>
                            </ul>
                        </li>
                        <li>Selecione o <strong>Papel</strong> do usuário</li>
                        <li>Marque se o usuário está <strong>Ativo</strong></li>
                        <li>Clique em <strong>Salvar</strong></li>
                        <li>O usuário receberá um email com senha temporária</li>
                    </ol>
                </div>
            </div>

            <!-- Editar Usuário -->
            <div class="card mb-4" id="editar-usuario">
                <div class="card-header">
                    <h5 class="mb-0">Como Editar Usuário</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Acesse o menu <strong>Usuários</strong></li>
                        <li>Encontre o usuário na lista</li>
                        <li>Clique no botão <strong>Editar</strong></li>
                        <li>Altere os dados necessários</li>
                        <li>Para alterar papel, selecione novo no campo <strong>Papel</strong></li>
                        <li>Clique em <strong>Salvar</strong></li>
                    </ol>
                    <div class="alert alert-warning">
                        <strong>Atenção:</strong> Alterar o papel de um usuário pode afetar suas permissões no sistema.
                    </div>
                </div>
            </div>

            <!-- Ativar/Desativar -->
            <div class="card mb-4" id="ativar-usuario">
                <div class="card-header">
                    <h5 class="mb-0">Como Ativar/Desativar Usuário</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Acesse o menu <strong>Usuários</strong></li>
                        <li>Encontre o usuário na lista</li>
                        <li>Clique no botão <strong>Editar</strong></li>
                        <li>Marque ou desmarque a opção <strong>Ativo</strong></li>
                        <li>Clique em <strong>Salvar</strong></li>
                    </ol>
                    <div class="alert alert-info">
                        <strong>Dica:</strong> Usuários inativos não conseguem fazer login no sistema.
                    </div>
                </div>
            </div>

            <!-- Resetar Senha -->
            <div class="card mb-4" id="resetar-senha">
                <div class="card-header">
                    <h5 class="mb-0">Como Resetar Senha</h5>
                </div>
                <div class="card-body">
                    <p>Para resetar a senha de um usuário:</p>
                    <ol>
                        <li>Oriente o usuário a acessar a página de login</li>
                        <li>Clicar em <strong>"Esqueci minha senha"</strong></li>
                        <li>Informar o email cadastrado</li>
                        <li>Seguir instruções do email recebido</li>
                    </ol>
                    <div class="alert alert-info">
                        <strong>Alternativa:</strong> Administradores podem editar o usuário e gerar nova senha temporária.
                    </div>
                </div>
            </div>

            <!-- Papéis -->
            <div class="card mb-4" id="definir-papel">
                <div class="card-header">
                    <h5 class="mb-0">Papéis do Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Papel</th>
                                    <th>Descrição</th>
                                    <th>Principais Permissões</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>SuperAdmin</strong></td>
                                    <td>Administrador geral</td>
                                    <td>Acesso total ao sistema</td>
                                </tr>
                                <tr>
                                    <td><strong>Admin</strong></td>
                                    <td>Administrador</td>
                                    <td>Gerencia usuários e dados</td>
                                </tr>
                                <tr>
                                    <td><strong>Profissional</strong></td>
                                    <td>Médico/Psicólogo</td>
                                    <td>Avalia crianças, cria checklists</td>
                                </tr>
                                <tr>
                                    <td><strong>Pais</strong></td>
                                    <td>Responsável</td>
                                    <td>Visualiza apenas seus filhos</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection