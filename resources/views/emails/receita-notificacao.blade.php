<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notificacao de Receita</title>
</head>
<body>
    <h1>Receita {{ $actionLabel }}</h1>

    <p>Uma receita foi {{ $actionLabel }} com os dados abaixo:</p>

    <ul>
        <li><strong>Nome:</strong> {{ $receita->nome }}</li>
        <li><strong>Descricao:</strong> {{ $receita->descricao ?: '-' }}</li>
        <li><strong>Data:</strong> {{ optional($receita->data_registro)->format('d/m/Y') }}</li>
        <li><strong>Custo:</strong> R$ {{ number_format((float) $receita->custo, 2, ',', '.') }}</li>
        <li><strong>Tipo:</strong> {{ ucfirst($receita->tipo_receita) }}</li>
        <li><strong>Status:</strong> {{ ucfirst($receita->status) }}</li>
    </ul>
</body>
</html>
