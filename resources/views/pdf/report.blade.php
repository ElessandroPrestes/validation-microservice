<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Usuário</title>
</head>
<body>
    <h1>Relatório de Usuário</h1>
    <ul>
        <li><strong>CPF:</strong> {{ $user->cpf }}</li>
        <li><strong>CEP:</strong> {{ $user->cep }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Status do CPF:</strong> {{ $user->cpf_status }}</li>
        <li><strong>Risco:</strong> {{ $risk }}</li>
    </ul>
</body>
</html>
