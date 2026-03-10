@extends('emails.layout')

@section('title', 'Bem-vindo ao ' . $appName)

@section('content')
    <h2>Bem-vindo(a), {{ $user->name }}</h2>
    <p>Sua conta foi criada com sucesso. Utilize os dados abaixo para acessar o sistema.</p>

    <h3>Dados de acesso</h3>
    <div class="info-box">
        <table class="data-table">
            <tr>
                <td>E-mail</td>
                <td><strong>{{ $user->email }}</strong></td>
            </tr>
            <tr>
                <td>Senha</td>
                <td><code>{{ $password ?? 'Solicite ao administrador' }}</code></td>
            </tr>
        </table>
    </div>

    <p style="text-align: center; margin: 24px 0;">
        <a href="{{ $appUrl }}" class="button">Acessar o Sistema</a>
    </p>

    <div class="notice">
        <p><strong>Importante:</strong> Por seguranca, altere sua senha apos o primeiro acesso.</p>
    </div>

    @if($user->phone || $user->street || $user->city)
    <h3>Dados cadastrados</h3>
    <table class="data-table">
        <tr>
            <td>Nome</td>
            <td>{{ $user->name }}</td>
        </tr>
        @if($user->phone)
        <tr>
            <td>Telefone</td>
            <td>{{ $user->phone }}</td>
        </tr>
        @endif
        @if($user->street)
        <tr>
            <td>Endereco</td>
            <td>
                {{ $user->street }}@if($user->number), {{ $user->number }}@endif
                @if($user->complement) - {{ $user->complement }}@endif
                @if($user->neighborhood)<br>{{ $user->neighborhood }}@endif
                @if($user->city) - {{ $user->city }}@endif
                @if($user->state)/{{ $user->state }}@endif
                @if($user->postal_code) - CEP {{ $user->postal_code }}@endif
            </td>
        </tr>
        @endif
    </table>
    @endif

    <p>Em caso de duvida, entre em contato com a equipe de suporte.</p>
@endsection
