<!DOCTYPE html>
<html>
<head>
    <title>Conta Atualizada</title>
</head>
<body>
    <h2>Olá, {{ $user->name }}!</h2>
    <p>Suas informações foram atualizadas com sucesso.</p>
    <p>Data da atualização: <strong>{{ $user->updated_at }}</strong></p>

    <h3>Seus dados</h3>
    <p><strong>Nome:</strong> {{ $user->name }}<br>
    <strong>E-mail:</strong> {{ $user->email }}<br>
    <strong>Telefone:</strong> {{ $user->phone ?? 'Não informado' }}</p>

    <h3>Endereço</h3>
    <p><strong>Rua:</strong> {{ $user->street ?? 'Não informado' }}<br>
    <strong>Número:</strong> {{ $user->number ?? 'Não informado' }}<br>
    <strong>Complemento:</strong> {{ $user->complement ?? 'Não informado' }}<br>
    <strong>Bairro:</strong> {{ $user->neighborhood ?? 'Não informado' }}<br>
    <strong>Cidade:</strong> {{ $user->city ?? 'Não informado' }}<br>
    <strong>Estado:</strong> {{ $user->state ?? 'Não informado' }}<br>
    <strong>CEP:</strong> {{ $user->postal_code ?? 'Não informado' }}</p>

    <p>Se você não realizou esta alteração, por favor entre em contato com nosso suporte.</p>

    <p>&nbsp;</p>
    <p>Atenciosamente, <br>
        Equipe de Suporte
        </p>

    <hr>

    <!-- Rodapé -->
    <footer>
        <p><strong>{{ config('app.name') }}</strong> - {{ env('APP_DESCRIPTION') }}</p>
        <p>Versão do sistema: {{ env('APP_VERSION') }}</p>
        <p>&copy; {{ date('Y') }} Todos os direitos reservados.</p>
    </footer>




</body>
</html>
