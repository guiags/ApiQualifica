<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PessoaController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validação rigorosa dos dados recebidos do App Mobile
        $validados = $request->validate([
            'cpf' => 'required|string|max:14|unique:pessoas_cadastradas,cpf',
            'rg' => 'required|string|max:20',
            'nome' => 'required|string|max:255',
            'nome_mae' => 'required|string|max:255',
            'endereco' => 'required|string',
            'estado_civil' => 'required|string|max:30',
            'profissao' => 'required|string|max:100',
            'escolaridade' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'telefone' => 'required|string|max:20',
            'contato' => 'required|string|max:150',
            'possui_passagem_criminal' => 'required|boolean',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:4096', // Limite de 4MB
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            // 2. Upload da foto de forma segura (Armazenada na pasta storage/app/public/fotos)
            if ($request->hasFile('foto')) {
                $caminhoFoto = $request->file('foto')->store('fotos', 'public');
                // Gera a URL pública para o aplicativo mobile conseguir acessar depois
                $validados['foto_url'] = Storage::url($caminhoFoto);
            }

            // 3. Vincula automaticamente o ID do usuário autenticado no App
            $validados['criado_por'] = $request->user()->id;

            // 4. Salva no banco de dados através do Model
            $pessoa = Pessoa::create($validados);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Cadastro realizado com sucesso.',
                'dados' => $pessoa
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Incapaz de salvar o registo.',
                'detalhe' => $e->getMessage()
            ], 500);
        }
    }
}