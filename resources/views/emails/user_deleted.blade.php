@extends('emails.layout')

@section('title', 'Conta Desativada - ' . $appName)

@section('content')
    <h2>Conta desativada</h2>
    <p>Ola, {{ $user->name }}. Sua conta foi desativada em nosso sistema.</p>

    <div class="info-box">
        <table class="data-table">
            <tr>
                <td>Data</td>
                <td>{{ $user->deleted_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <h3>Dados da conta</h3>
    <table class="data-table">
        <tr>
            <td>Nome</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td>E-mail</td>
            <td>{{ $user->email }}</td>
        </tr>
        @if($user->phone)
        <tr>
            <td>Telefone</td>
            <td>{{ $user->phone }}</td>
        </tr>
        @endif
    </table>

    <div class="notice">
        <p>Se acredita que esta desativacao foi realizada por engano, entre em contato com a equipe de suporte para reativar sua conta.</p>
    </div>

    <p>Agradecemos por ter utilizado nossos servicos.</p>
@endsection
