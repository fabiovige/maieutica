@extends('emails.layout')

@section('title', 'Bem-vindo ao ' . $appName)

@section('content')
    <h2>Olá, {{ $user->name }}! 👋</h2>
    <p>Seja bem-vindo(a) ao <strong>{{ $appName }}</strong>!</p>
    <p>Sua conta foi criada com sucesso e você já pode acessar o sistema.</p>

    <h3>🔐 Seus dados de acesso</h3>
    <div class="info-box">
        <p><strong>E-mail:</strong> {{ $user->email }}<br>
        <strong>Senha temporária:</strong> <code style="background: #e9ecef; padding: 5px 10px; border-radius: 3px;">{{ $password ?? 'Solicite ao administrador' }}</code></p>
    </div>

    <a href="{{ $appUrl }}" class="button">Acessar o Sistema</a>

    <div class="warning">
        <strong>⚠️ Importante:</strong> Por questões de segurança, recomendamos que você altere sua senha após o primeiro acesso.
    </div>

    <h3>👤 Seus dados cadastrados</h3>
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

    <p>Se você tiver qualquer dúvida, entre em contato com nossa equipe de suporte.</p>

    <p>Atenciosamente,<br>
    <strong>Equipe {{ $appName }}</strong></p>
@endsection
