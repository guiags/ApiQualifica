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
        // 1. Validação agora espera um array de fotos
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
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            // Valida que o campo é um array de imagens
            'fotos' => 'required|array|min:1', 
            'fotos.*' => 'image|mimes:jpeg,png,jpg|max:4096', 
        ]);

        try {
            // 2. Removemos as fotos do array $validados para criar a Pessoa primeiro
            $dadosPessoa = $request->except('fotos');
            $dadosPessoa['criado_por'] = $request->user()->id;

            // 3. Salva a Pessoa
            $pessoa = Pessoa::create($dadosPessoa);

            // 4. Loop para processar múltiplas fotos
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $foto) {
                    $caminho = $foto->store('fotos', 'public');
                    
                    // Salva na tabela 'fotos' (relacionada a esta pessoa)
                    $pessoa->fotos()->create([
                        'url' => Storage::url($caminho)
                    ]);
                }
            }

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Cadastro realizado com sucesso.',
                'dados' => $pessoa->load('fotos') // Retorna a pessoa com a lista de fotos vinculadas
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Incapaz de salvar o registro.',
                'detalhe' => $e->getMessage()
            ], 500);
        }
    }


    public function index(Request $request)
    {
        try {
            // Buscamos as pessoas criadas pelo usuário logado
            // O 'with('fotos')' carrega as fotos automaticamente junto com a pessoa
            $pessoas = Pessoa::with('fotos')
                ->where('criado_por', $request->user()->id)
                ->orderBy('created_at', 'desc') // Mostra os mais recentes primeiro
                ->get();

            return response()->json([
                'status' => 'sucesso',
                'dados' => $pessoas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao buscar registros.',
                'detalhe' => $e->getMessage()
            ], 500);
        }
    }

    // Método para Atualizar
    public function update(Request $request, $id)
    {
        try {
            $pessoa = Pessoa::where('criado_por', $request->user()->id)->findOrFail($id);
            
            // Validação ajustada para o cenário de Edição
            $validados = $request->validate([
                // CORREÇÃO 1: Força o Laravel a ignorar este registro atual na checagem de duplicidade
                'cpf' => 'required|string|max:14|unique:pessoas_cadastradas,cpf,' . $id,
                'rg' => 'required|string|max:20',
                'nome' => 'required|string|max:255',
                'nome_mae' => 'required|string|max:255',
                'endereco' => 'required|string',
                'estado_civil' => 'required|string|max:30',
                'profissao' => 'nullable|string|max:100',
                'escolaridade' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:150',
                'telefone' => 'required|string|max:20',
                'contato' => 'nullable|string|max:150',
                'possui_passagem_criminal' => 'required|boolean',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                // CORREÇÃO 2: Usa 'sometimes' para que a validação não falhe se o app não enviar fotos no PUT
                'fotos' => 'sometimes|array|min:1', 
                'fotos.*' => 'image|mimes:jpeg,png,jpg|max:4096'
            ]);
            
            // CORREÇÃO 3: Filtra o array para não tentar salvar a chave 'fotos' direto na tabela de pessoas
            $dadosParaSalvar = \Illuminate\Support\Arr::except($validados, ['fotos']);

            // Executa a atualização com os dados limpos
            $pessoa->update($dadosParaSalvar);

            return response()->json([
                'status' => 'sucesso', 
                'dados' => $pessoa->load('fotos')
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // CORREÇÃO 4: Captura específica de validação para devolver os campos com erro ao Flutter
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Dados inválidos.',
                'erros' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'erro', 
                'mensagem' => $e->getMessage()
            ], 500);
        }
    }

    // Método para Excluir
    public function destroy(Request $request, $id)
    {
        try {
            $pessoa = Pessoa::where('criado_por', $request->user()->id)->findOrFail($id);
            $pessoa->delete(); // O 'onDelete cascade' no banco apagará as fotos vinculadas

            return response()->json(['status' => 'sucesso', 'mensagem' => 'Registro excluído.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'erro', 'mensagem' => $e->getMessage()], 500);
        }
    }
}