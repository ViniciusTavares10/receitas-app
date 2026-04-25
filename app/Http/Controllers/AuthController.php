<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'senha' => ['required', 'string'],
        ]);

        $user = Usuario::where('login', $credentials['login'])
            ->where('senha', $credentials['senha'])
            ->where('situacao', true)
            ->first();

        if ($user) {
            session(['usuario' => $user]);

            return redirect('/receitas');
        }

        return back()->with('erro', 'Login inválido');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'login' => ['required', 'string', 'max:50', Rule::unique('usuario', 'login')],
            'senha' => ['required', 'string', 'max:100'],
        ]);

        Usuario::create([
            ...$data,
            'situacao' => true,
        ]);

        return back()->with('success', 'Cadastro realizado com sucesso. Agora voce ja pode entrar.');
    }
}
