<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pessoa extends Model
{
    // 1. Especificamos o nome da tabela pois ela é diferente de 'pessoas'
    protected $table = 'pessoas_cadastradas';

    // 2. Definimos os campos que podem ser preenchidos via 'create'
    protected $fillable = [
        'cpf',
        'rg',
        'nome',
        'nome_mae',
        'endereco',
        'estado_civil',
        'profissao',
        'escolaridade',
        'email',
        'telefone',
        'contato',
        'possui_passagem_criminal',
        'latitude',
        'longitude',
        'criado_por', // ID do usuário que fez o cadastro
    ];

    // 3. Conversão de tipos: garante que o campo booleano seja tratado como true/false
    protected $casts = [
        'possui_passagem_criminal' => 'boolean',
    ];

    // 4. Relacionamento: Uma pessoa possui várias fotos
    public function fotos(): HasMany
    {
        return $this->hasMany(Foto::class, 'pessoa_id');
    }
}