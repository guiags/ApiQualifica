<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PessoaController;


// Rota pública para autenticação (Login)
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas (Exigem o Token de autenticação Bearer no cabeçalho)
Route::middleware('auth:sanctum')->group(function () {
    
    // Rota para efetuar o cadastro completo vindo do aplicativo
    Route::post('/pessoas', [PessoaController::class, 'store']);
    
});