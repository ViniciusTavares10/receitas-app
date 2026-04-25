<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial; text-align: center; margin-top: 100px;">

    <h2>Login do Sistema</h2>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

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

    <hr style="max-width: 320px; margin: 40px auto;">

    <h3>Cadastro do Cliente</h3>

    <form method="POST" action="{{ route('auth.register') }}">
        @csrf

        <div>
            <input type="text" name="nome" placeholder="Nome" value="{{ old('nome') }}" required>
        </div><br>

        <div>
            <input type="email" name="email" placeholder="E-mail" value="{{ old('email') }}" required>
        </div><br>

        <div>
            <input type="text" name="login" placeholder="Login" value="{{ old('login') }}" required>
        </div><br>

        <div>
            <input type="password" name="senha" placeholder="Senha" required>
        </div><br>

        <button type="submit">Cadastrar</button>
    </form>

    @if($errors->any())
        <div style="color: red; margin-top: 20px;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

</body>
</html>
