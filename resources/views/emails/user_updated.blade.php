@extends('emails.layout')

@section('title', 'Conta Atualizada - ' . $appName)

@section('content')
    <h2>Olá, {{ $user->name }}! ✅</h2>
    <p>Suas informações foram <strong>atualizadas com sucesso</strong> em nosso sistema.</p>

    <div class="info-box">
        <p><strong>📅 Data da atualização:</strong> {{ $user->updated_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <h3>👤 Seus dados atuais</h3>
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

    <div class="warning">
        <strong>🔒 Segurança:</strong> Se você não realizou esta alteração, entre em contato com nossa equipe de suporte imediatamente.
    </div>

    <a href="{{ $appUrl }}" class="button">Acessar o Sistema</a>

    <p>Atenciosamente,<br>
    <strong>Equipe {{ $appName }}</strong></p>
@endsection
