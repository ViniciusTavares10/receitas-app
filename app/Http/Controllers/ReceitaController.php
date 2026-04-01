<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receita;

class ReceitaController extends Controller
{
    public function index()
    {
        $receitas = Receita::all();
        return view('receitas', compact('receitas'));
    }

    public function store(Request $request)
    {
        Receita::create($request->all());
        return redirect('/receitas');
    }

    public function destroy($id)
    {
        Receita::findOrFail($id)->delete();
        return redirect('/receitas');
    }

    public function update(Request $request, $id)
    {
        $receita = Receita::findOrFail($id);
        $receita->update($request->all());

        return redirect('/receitas');
    }
}