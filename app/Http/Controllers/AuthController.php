<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function login(Request $request) {
        $user = Usuario::where('login', $request->login)
                    ->where('senha', $request->senha)
                    ->first();

        if ($user) {
            session(['usuario' => $user]);
            return redirect('/receitas');
        }

        return back()->with('erro', 'Login inválido');
    }
}
