<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReceitaController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/cadastro', [AuthController::class, 'register'])->name('auth.register');

Route::get('/receitas', [ReceitaController::class, 'index'])->name('receitas.index');
Route::get('/receitas/pdf', [ReceitaController::class, 'exportPdf'])->name('receitas.export-pdf');
Route::post('/receitas', [ReceitaController::class, 'store'])->name('receitas.store');
Route::put('/receitas/{id}', [ReceitaController::class, 'update'])->name('receitas.update');
Route::delete('/receitas/{id}', [ReceitaController::class, 'destroy'])->name('receitas.destroy');
