<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceitaController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('login');
});

Route::post('/login', [AuthController::class, 'login']);

Route::get('/receitas', [ReceitaController::class, 'index']);
Route::post('/receitas', [ReceitaController::class, 'store']);
Route::put('/receitas/{id}', [ReceitaController::class, 'update']);
Route::delete('/receitas/{id}', [ReceitaController::class, 'destroy']);
