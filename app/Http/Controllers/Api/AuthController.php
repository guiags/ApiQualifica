<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Valida se o App enviou matrícula e senha corretamente
        $request->validate([
            'matricula' => 'required|size:7',
            'password' => 'required',
        ]);

        // 2. Tenta fazer o login com as credenciais fornecidas
        if (!Auth::attempt($request->only('matricula', 'password'))) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Credenciais inválidas. Verifique sua matrícula e senha.'
            ], 401); // 401 Unauthorized
        }

        // 3. Se as credenciais estiverem corretas, busca o usuário no banco
        $user = User::where('matricula', $request->matricula)->firstOrFail();

        // 4. Cria o Token de acesso via Laravel Sanctum
        $token = $user->createToken('auth_token_mobile')->plainTextToken;

        // 5. Devolve o Token para o aplicativo mobile guardar
        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Login aprovado.',
            'dados_usuario' => [
                'id' => $user->id,
                'nome' => $user->name,
                'matricula' => $user->matricula,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}