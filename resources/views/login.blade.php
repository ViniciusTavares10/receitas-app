<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial; text-align: center; margin-top: 100px;">

    <h2>Login do Sistema</h2>

    <form method="POST" action="/login">
        @csrf

        <div>
            <input type="text" name="login" placeholder="Login" required>
        </div><br>

        <div>
            <input type="password" name="senha" placeholder="Senha" required>
        </div><br>

        <button type="submit">Entrar</button>
    </form>

    @if(session('erro'))
        <p style="color: red;">{{ session('erro') }}</p>
    @endif

</body>
</html>