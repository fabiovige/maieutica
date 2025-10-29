@extends('emails.layout')

@section('title', 'Conta Desativada - ' . $appName)

@section('content')
    <h2>Olá, {{ $user->name }}</h2>
    <p>Sua conta foi <strong>desativada</strong> em nosso sistema.</p>

    <div class="warning">
        <p><strong>📅 Data da desativação:</strong> {{ $user->deleted_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <h3>👤 Dados da conta desativada</h3>
    <p>
        <strong>Nome:</strong> {{ $user->name }}<br>
        <strong>E-mail:</strong> {{ $user->email }}<br>
        <strong>Telefone:</strong> {{ $user->phone ?? 'Não informado' }}
    </p>

    @if($user->street || $user->city || $user->state || $user->postal_code)
    <h3>📍 Endereço</h3>
    <p>
        @if($user->street)<strong>Rua:</strong> {{ $user->street }}<br>@endif
        @if($user->number)<strong>Número:</strong> {{ $user->number }}<br>@endif
        @if($user->complement)<strong>Complemento:</strong> {{ $user->complement }}<br>@endif
        @if($user->neighborhood)<strong>Bairro:</strong> {{ $user->neighborhood }}<br>@endif
        @if($user->city)<strong>Cidade:</strong> {{ $user->city }}<br>@endif
        @if($user->state)<strong>Estado:</strong> {{ $user->state }}<br>@endif
        @if($user->postal_code)<strong>CEP:</strong> {{ $user->postal_code }}@endif
    </p>
    @endif

    <div class="info-box">
        <p><strong>ℹ️ Informação:</strong> Se você acredita que esta desativação foi realizada por engano, entre em contato com nossa equipe de suporte para reativar sua conta.</p>
    </div>

    <p>Agradecemos por ter utilizado nossos serviços.</p>

    <p>Atenciosamente,<br>
    <strong>Equipe {{ $appName }}</strong></p>
@endsection
