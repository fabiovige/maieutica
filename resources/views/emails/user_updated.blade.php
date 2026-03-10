@extends('emails.layout')

@section('title', 'Conta Atualizada - ' . $appName)

@section('content')
    <h2>Dados atualizados</h2>
    <p>Ola, {{ $user->name }}. Suas informacoes foram atualizadas com sucesso.</p>

    <div class="info-box">
        <table class="data-table">
            <tr>
                <td>Data</td>
                <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <h3>Dados atuais</h3>
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

    <div class="notice">
        <p><strong>Seguranca:</strong> Se voce nao realizou esta alteracao, entre em contato com o suporte imediatamente.</p>
    </div>

    <p style="text-align: center; margin: 24px 0;">
        <a href="{{ $appUrl }}" class="button">Acessar o Sistema</a>
    </p>
@endsection
